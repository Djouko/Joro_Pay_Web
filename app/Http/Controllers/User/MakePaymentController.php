<?php

namespace App\Http\Controllers\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Merchants\Merchant;
use App\Models\Merchants\MerchantNotification;
use App\Models\Merchants\MerchantWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\MakePayment\ReceiverMail;
use App\Notifications\User\MakePayment\SenderMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\PushNotificationHelper;
use App\Notifications\Admin\ActivityNotification;

class MakePaymentController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'MP'.getTrxNum();
    }
    public function index() {
        $page_title = __("Make Payment");
        $makePaymentCharge = TransactionSetting::where('slug','make-payment')->where('status',1)->first();
        $transactions = Transaction::auth()->makePayment()->latest()->take(10)->get();
        return view('user.sections.make-payment.index',compact("page_title",'makePaymentCharge','transactions'));
    }
    public function checkUser(Request $request){
        $email = $request->email;
        $exist['data'] = Merchant::where('email',$email)->active()->first();

        $user = userGuard()['user'];
        if(@$exist['data'] && $user->email == @$exist['data']->email){
            return response()->json(['own'=>__("Can't transfer/request to your own")]);
        }
        return response($exist);
    }
    public function confirmed(Request $request){
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'email' => 'required|email'
        ]);
        $basic_setting = BasicSettings::first();
        $user = userGuard()['user'];
        $amount = $request->amount;
        $makePaymentCharge = TransactionSetting::where('slug','make-payment')->where('status',1)->first();
        $userWallet = UserWallet::where('user_id',$user->id)->first();
        if(!$userWallet){
            return back()->with(['error' => [__('User wallet not found')]]);
        }
        $baseCurrency = Currency::default();
        if(!$baseCurrency){
            return back()->with(['error' => [__('Default currency not found')]]);
        }
        $rate = $baseCurrency->rate;
        $receiver = Merchant::where('email', $request->email)->first();
        if(!$receiver){
            return back()->with(['error' => [__('Receiver not exist')]]);
        }
        $receiverWallet = MerchantWallet::where('merchant_id',$receiver->id)->first();
        if(!$receiverWallet){
            return back()->with(['error' => [__('Receiver wallet not found')]]);
        }

        $minLimit =  $makePaymentCharge->min_limit *  $rate;
        $maxLimit =  $makePaymentCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
        }
        //charge calculations
        $fixedCharge = $makePaymentCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $makePaymentCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        $recipient = $amount;
        if($payable > $userWallet->balance ){
            return back()->with(['error' => [__('Sorry, insufficient balance')]]);
        }

        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender( $trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver);
            if($sender){
                 $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$sender,$receiver);
            }
            //Sender notifications
            try{
                if( $basic_setting->email_notification == true){
                    $notifyDataSender = [
                        'trx_id'  => $trx_id,
                        'title'  => __("Make Payment to")." @" . @$receiver->username." (".@$receiver->email.")",
                        'request_amount'  => getAmount($amount,4).' '.get_default_currency_code(),
                        'payable'   =>  getAmount($payable,4).' ' .get_default_currency_code(),
                        'charges'   => getAmount( $total_charge, 2).' ' .get_default_currency_code(),
                        'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                        'status'  => __("success"),
                    ];
                    //sender notifications
                    $user->notify(new SenderMail($user,(object)$notifyDataSender));
                }
            }catch(Exception $e){
                //Error Handle
            }
            $receiverTrans = $this->insertReceiver( $trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$receiverWallet);
            if($receiverTrans){
                 $this->insertReceiverCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$receiverTrans,$receiver);
            }
            try{
                if( $basic_setting->email_notification == true){
                    //Receiver notifications
                    $notifyDataReceiver = [
                        'trx_id'  => $trx_id,
                      'title'  => __("Make Payment From")." @" .@$user->username." (".@$user->email.")",
                        'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                        'status'  => __("success"),
                    ];
                    //send notifications
                    $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
                }
            }catch(Exception $e){
            //Error Handle
            }
            $this->adminNotification($trx_id,$total_charge,$amount,$payable,$user,$receiver);

            return redirect()->route("user.make.payment.index")->with(['success' => [__('Make Payment successful to').' '.$receiver->fullname]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }

     //sender transaction
    public function insertSender($trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'recipient_amount' => $recipient,
            'receiver' => $receiver,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPEMAKEPAYMENT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMAKEPAYMENT," ")) . " To " .$receiver->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Make Payment"),
                'message'       => __("Payment To ")." ".$receiver->fullname.' ' .$amount.' '.get_default_currency_code()." ".__("Successful"),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::MAKE_PAYMENT,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            (new PushNotificationHelper())->prepare([$user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$receiverWallet) {
        $trx_id = $trx_id;
        $receiverWallet = $receiverWallet;
        $recipient_amount = ($receiverWallet->balance + $recipient);
        $details =[
            'sender_amount' => $amount,
            'sender' => $user,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'merchant_id'                       => $receiver->id,
                'merchant_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPEMAKEPAYMENT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $recipient_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMAKEPAYMENT," ")) . " From " .$user->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Make Payment"),
                'message'       => __("Payment From")." ".$user->fullname.' ' .$amount.' '.get_default_currency_code()." ".__("Successful"),
                'image'         => get_image($receiver->image,'merchant-profile'),
            ];

            MerchantNotification::create([
                'type'      => NotificationConst::MAKE_PAYMENT,
                'merchant_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            (new PushNotificationHelper())->prepare([$receiver->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'merchant',
            ])->send();

            //admin notification
            $notification_content['title'] = __("Make Payment From")." ".$user->fullname.' ' .$amount.' '.get_default_currency_code().' '.__("Successful").' ('.$receiver->username.')';
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //admin notification
    public function adminNotification($trx_id,$total_charge,$amount,$payable,$user,$receiver){
        $notification_content = [
            //email notification
            'subject' => __("Make Payment to")." @" . @$receiver->username." (".@$receiver->email.")",
            'greeting' =>__("Make Payment Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$user->email."<br>".__("Receiver").": @".$receiver->email."<br>".__("Sender Amount")." : ".get_amount($amount,get_default_currency_code())."<br>".__("Fees & Charges")." : ".get_amount($total_charge,get_default_currency_code())."<br>".__("Total Payable Amount")." : ".get_amount($payable,get_default_currency_code())."<br>".__("Recipient Received")." : ".get_amount($amount,get_default_currency_code())."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Make Payment")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$user->email." ".__("Receiver").": @".$receiver->email." ".__("Sender Amount")." : ".get_amount($amount,get_default_currency_code())." ".__("Receiver Amount")." : ".get_amount($amount,get_default_currency_code()),

            //admin db notification
            'notification_type' =>  NotificationConst::MAKE_PAYMENT,
            'admin_db_title' => "Make Payment"." (".userGuard()['type'].")",
            'admin_db_message' =>"Sender".": @".$user->email.","."Receiver".": @".$receiver->email.","."Sender Amount"." : ".get_amount($amount,get_default_currency_code()).","."Receiver Amount"." : ".get_amount($amount,get_default_currency_code())
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.make.payment.index','admin.make.payment.export.data'])
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
}
