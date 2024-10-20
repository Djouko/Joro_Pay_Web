<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\RequestMoney\ReceiverMail;
use App\Notifications\User\RequestMoney\SenderMail;

class RequestMoneyController extends Controller
{
   public function index()
   {
       $page_title = __('request Money');
       $user = auth()->user();
       $sender_wallets = UserWallet::auth()->whereHas('currency',function($q) {
           $q->where("sender",GlobalConst::ACTIVE)->where("status",GlobalConst::ACTIVE);
       })->active()->get();
       $charges =TransactionSetting::where('slug','request-money')->where('status',1)->first();
       $transactions = Transaction::auth()->requestMoney()->orderByDesc("id")->get();
       return view('user.sections.request-money.index',compact('page_title','sender_wallets','charges','transactions'));
   }
    public function checkUser(Request $request){
        $email = $request->email;
        $exist['data'] = User::where('email',$email)->active()->first();

        $user = auth()->user();
        if(@$exist['data'] && $user->email == @$exist['data']->email){
            return response()->json(['own'=>__("Can't Request Money To Your Own")]);
        }
        return response($exist);
    }
    public function submit(Request $request) {
        $validated = Validator::make($request->all(),[
            'request_amount'    => "required|numeric|gt:0",
            'currency'          => "required|string|exists:currencies,code",
            'email'             => "required|string",
            'remark'            => "nullable|string|max:300"
        ])->validate();
        $basic_setting = BasicSettings::first();
        $trx_id = 'RM'.getTrxNum();
        $sender_wallet = UserWallet::auth()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['currency'])->active();
        })->active()->first();

        if(!$sender_wallet) return back()->with(['error' => [__("Your wallet isn't available with currency").' ('.$validated['currency'].')']]);

        $receiver_currency = Currency::receiver()->active()->where('code',$validated['currency'])->first();

        $trx_charges = TransactionSetting::where('slug','request-money')->where('status',1)->first();
        $charges = $this->requestMoneyCharge($validated['request_amount'],$trx_charges,$sender_wallet,$receiver_currency);

        // Check transaction limit
        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;
        if($charges['request_amount'] < $min_amount || $charges['request_amount'] > $max_amount) {
            return back()->with(['error' => [__('Please follow the transaction limit').' (Min '.$min_amount . ' ' . $sender_wallet->currency->code .' - Max '.$max_amount. ' ' . $sender_wallet->currency->code . ')']]);
        }

        $field_name = "username";
        if(check_email($validated['email'])) {
            $field_name = "email";
        }

        $receiver = User::where($field_name,$validated['email'])->active()->first();
        if(!$receiver) return back()->with(['error' => [__("Receiver doesn't exists or Receiver is temporary banned")]]);
        if($receiver->username == $sender_wallet->user->username || $receiver->email == $sender_wallet->user->email) return back()->with(['error' => [__("Can't Request Money To Your Own")]]);

        $receiver_wallet = UserWallet::where("user_id",$receiver->id)->whereHas("currency",function($q) use ($receiver_currency){
            $q->receiver()->where("code",$receiver_currency->code);
        })->first();

        if(!$receiver_wallet) return back()->with(['error' => [__('Receiver wallet not found')]]);
        // if($charges['payable'] > $sender_wallet->balance) return back()->with(['error' => [__('Your Wallet Balance Is Insufficient')]]);
        DB::beginTransaction();

        try{

            $trx_details = [
                'receiver_username'     => $receiver_wallet->user->username,
                'receiver_email'        => $receiver_wallet->user->email,
                'receiver_fullname'     => $receiver_wallet->user->fullname,
                'sender_username'       => $sender_wallet->user->username,
                'sender_email'          => $sender_wallet->user->email,
                'sender_fullname'       => $sender_wallet->user->fullname,
                'charges'               => $charges,
            ];


            // Sender TRX
            $sender = DB::table("transactions")->insertGetId([
                'user_id'           => $sender_wallet->user->id,
                'user_wallet_id'    => $sender_wallet->id,
                'type'              => PaymentGatewayConst::REQUESTMONEY,
                'trx_id'            => $trx_id,
                'request_amount'    => $charges['request_amount'],
                'payable'           => $charges['request_amount'],
                'available_balance' => $sender_wallet->balance,
                'attribute'         => PaymentGatewayConst::SEND,
                'details'           => json_encode($trx_details),
                'status'            => GlobalConst::PENDING,
                'remark'            => $validated['remark'],
                'created_at'        => now(),
            ]);
            if($sender){
                $this->insertSenderCharges($sender, (object)$charges,$sender_wallet->user,$receiver);
            }

            // Receiver TRX
            $receiverTrans = DB::table("transactions")->insertGetId([
                'user_id'          => $receiver_wallet->user->id,
                'user_wallet_id'   => $receiver_wallet->id,
                'type'              => PaymentGatewayConst::REQUESTMONEY,
                'trx_id'            => $trx_id,
                'request_amount'    => $charges['receiver_amount'],
                'payable'           => $charges['payable'],
                'available_balance' => $receiver_wallet->balance,
                'attribute'         => PaymentGatewayConst::RECEIVED,
                'details'           => json_encode($trx_details),
                'status'            => GlobalConst::PENDING,
                'remark'            => $validated['remark'],
                'created_at'        => now(),
            ]);

            if($receiverTrans){
                $this->insertReceiverCharges($receiverTrans,(object)$charges,$sender_wallet->user,$receiver);
            }
           if( $basic_setting->email_notification == true){
                //sender notifications
                $notifyDataSender = [
                    'trx_id'  => $trx_id,
                    'title'  => __("Request Money to")." @" . @$receiver->username." (".@$receiver->email.")",
                    'request_amount'  => getAmount($charges['request_amount'],4).' '.$charges['sender_currency'],
                    'payable'   =>  getAmount($charges['payable'],4).' ' .$charges['receiver_currency'],
                    'charges'   => getAmount( $charges['total_charge'], 2).' ' .$charges['receiver_currency'],
                    'will_get'  => getAmount( $charges['request_amount'], 2).' ' .$charges['sender_currency'],
                    'status'  => __("Pending"),
                ];
                try{
                    $sender_wallet->user->notify(new SenderMail( $sender_wallet->user,(object)$notifyDataSender));
                }catch(Exception $e){}

                //Receiver notifications
                $notifyDataReceiver = [
                    'trx_id'  => $trx_id,
                    'title'  => __("Request Money from")." @" . @$sender_wallet->user->username." (".@$sender_wallet->user->email.")",
                    'request_amount'  => getAmount($charges['request_amount'],4).' '.$charges['sender_currency'],
                    'payable'   =>  getAmount($charges['payable'],4).' ' .$charges['receiver_currency'],
                    'charges'   => getAmount( $charges['total_charge'], 2).' ' .$charges['receiver_currency'],
                    'will_get'  => getAmount( $charges['request_amount'], 2).' ' .$charges['sender_currency'],
                    'status'  => __("Pending"),
               ];
               //Receiver notifications
               try{
                   $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
               }catch(Exception $e){}

            }
            DB::commit();
            //admin notifications
            try{
                $this->adminNotification($trx_id,$charges,$sender_wallet->user,$receiver);
            }catch(Exception $e) {}
        }catch(Exception $e) {
            DB::rollBack();
            return redirect()->route('user.request.money.index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        }


        return redirect()->route('user.request.money.index')->with(['success' => [__('request Money Success')]]);
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$sender,$receiver){

        $notification_content = [
            //email notification
            'subject' =>__("request Money"),
            'greeting' =>__("Request Money Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$sender->email."<br>".__("Receiver").": @".$receiver->email."<br>".__("request Amount")." : ".get_amount($charges['request_amount'],$charges['sender_currency'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['receiver_currency'])."<br>".__("Recipient Received")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'])."<br>".__("Status")." : ".__("Pending"),

            //push notification
            'push_title' => __("Request Money Request Send")." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$sender->email." ".__("Receiver").": @".$receiver->email." ".__("Sender Amount")." : ".get_amount($charges['request_amount'],$charges['sender_currency'])." ".__("Receiver Amount")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency']),

            //admin db notification
            'notification_type' =>  NotificationConst::REQUEST_MONEY,
            'trx_id' =>  $trx_id,
            'admin_db_title' => "Request Money Request Send"." ".'Successful'." ".get_amount($charges['request_amount'],$charges['sender_currency'])." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$sender->email.","."Receiver".": @".$receiver->email.","."Sender Amount"." : ".get_amount($charges['request_amount'],$charges['sender_currency']).","."Receiver Amount"." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'])
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.request.money.index','admin.request.money.pending','admin.request.money.complete','admin.request.money.canceled','admin.request.money.export.data'])
                                    ->mail(ActivityNotification::class, [
                                        'subject'   => $notification_content['subject'],
                                        'greeting'  => $notification_content['greeting'],
                                        'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                        'user_type' => "admin",
                                        'title' => $notification_content['push_title'],
                                        'desc'  => $notification_content['push_content'],
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    //sender charges
    public function insertSenderCharges($id,$charges,$sender,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => 0,
                'fixed_charge'      => 0,
                'total_charge'      => 0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         =>__("request Money"),
                'message'       => __('Request Money to')." ".$receiver->fullname.' ' .$charges->request_amount.' '.$charges->sender_currency." ".__("Successful"),
                'image'         =>  get_image($sender->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::REQUEST_MONEY,
                'user_id'  => $sender->id,
                'message'   => $notification_content,
            ]);

             //Push Notifications
            (new PushNotificationHelper())->prepare([$sender->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //receiver Charge
    public function insertReceiverCharges($id,$charges,$sender,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      => $charges->fixed_charge,
                'total_charge'      => $charges->total_charge,
                'created_at'        => now(),
            ]);

            DB::commit();

            //store notification
            $notification_content = [
                'title'         =>__("request Money"),
                'message'       => __("Request Money from")." ".$sender->fullname.' ' .$charges->receiver_amount.' '.$charges->receiver_currency." ".__("Successful"),
                'image'         => get_image($receiver->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::REQUEST_MONEY,
                'user_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            (new PushNotificationHelper())->prepare([$receiver->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();



        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }

    public function requestMoneyCharge($request_amount,$charges,$sender_wallet,$receiver_currency) {

        $data['request_amount']          = $request_amount;
        $data['sender_currency']        = $sender_wallet->currency->code;
        $data['receiver_amount']        = $request_amount;
        $data['receiver_currency']      = $receiver_currency->code;
        $data['percent_charge']         = ($request_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  = $sender_wallet->balance;
        $data['payable']                = $request_amount + $data['total_charge'];
        return $data;
    }

     //manage transactions list
     public function logLists()
     {
         $page_title = __("Request Money Logs");
         $user = auth()->user();
         $transactions = Transaction::auth()->requestMoney()->orderByDesc("id")->paginate(10);
         return view('user.sections.request-money.logs.index',compact('page_title','transactions'));
     }

     public function approved(Request $request) {
         $validated = Validator::make($request->all(),[
             'target'        => "required|numeric",
         ])->validate();
         $transaction = Transaction::where('id', $validated['target'])->requestMoney()->pending()->first();
         if(!$transaction){
             return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
         }
         //request sender
         $sender = User::where('email',$transaction->details->sender_email)->first();
         $sender_currency =  $transaction->details->charges->sender_currency;
         $sender_wallet = UserWallet::where("user_id",$sender->id)->whereHas("currency",function($q) use ($sender_currency) {
             $q->where("code",$sender_currency)->active();
         })->active()->first();
         if(!$sender_wallet) return back()->with(['error' => [__("Sender wallet isn't available with currency").' ('.$sender_currency.')']]);
         //request receiver
         $receiver = User::where('email',$transaction->details->receiver_email)->first();
         $receiver_currency =  $transaction->details->charges->receiver_currency;
         $receiver_wallet = UserWallet::where("user_id",$receiver->id)->whereHas("currency",function($q) use ($receiver_currency){
             $q->receiver()->where("code",$receiver_currency);
         })->first();
         if(!$receiver_wallet) return back()->with(['error' => [__("Receiver wallet isn't available with currency").' ('.$receiver_currency.')']]);
         //receiver wallet balance check
         if( $transaction->payable > $receiver_wallet->balance) return back()->with(['error' => [__("Your wallet balance is insufficient")]]);
            DB::table($sender_wallet->getTable())->where("id",$sender_wallet->id)->update([
                'balance'           => ($sender_wallet->balance + $transaction->request_amount),
            ]);
            $receiver_wallet->refresh();
            DB::table($receiver_wallet->getTable())->where("id",$receiver_wallet->id)->update([
                'balance'           => ($receiver_wallet->balance - $transaction->payable),
            ]);
         //make success now both transactions
         $data =  Transaction::where('trx_id', $transaction->trx_id)->requestMoney()->pending()->get();
         try{
            foreach( $data as $val){
              $val->status = PaymentGatewayConst::STATUSSUCCESS;
              $val->save();
            }
         }catch(Exception $e) {
             return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
         }
         return back()->with(['success' => [__('Request approved Successfully!')]]);
     }
     public function rejected(Request $request) {
         $validated = Validator::make($request->all(),[
             'target'        => "required|numeric",
         ])->validate();
         $transaction = Transaction::where('id', $validated['target'])->requestMoney()->pending()->first();
         if(!$transaction){
             return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
         }

         //make rejected now both transactions
         $data =  Transaction::where('trx_id', $transaction->trx_id)->requestMoney()->pending()->get();
         try{
            foreach( $data as $val){
              $val->status = PaymentGatewayConst::STATUSREJECTED;
              $val->save();
            }
         }catch(Exception $e) {
             return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
         }
         return back()->with(['success' => [__('Money Request Rejected Successfully!')]]);
     }
}
