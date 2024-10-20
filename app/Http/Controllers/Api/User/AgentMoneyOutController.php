<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\TransactionSetting;
use App\Models\Agent;
use App\Models\AgentNotification;
use App\Models\AgentQrCode;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\MoneyOut\ReceiverMail;
use App\Notifications\User\MoneyOut\SenderMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgentMoneyOutController extends Controller
{
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'AMO'.getTrxNum();
    }
    public function index() {
        $user = authGuardApi()['user'];
        $moneyOutCharge = TransactionSetting::where('slug','money-out')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
                'monthly_limit' => getAmount($data->monthly_limit,2),
                'daily_limit' => getAmount($data->daily_limit,2),
                'agent_fixed_commissions' => getAmount($data->agent_fixed_commissions,2),
                'agent_percent_commissions' => getAmount($data->agent_percent_commissions,2),
                'agent_profit' => $data->agent_profit,
            ];
        })->first();
        $transactions = Transaction::auth()->agentMoneyOut()->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Money Out to @" . @$item->details->receiver_email,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                        'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                        'recipient_received' => getAmount(@$item->details->recipient_amount,2).' '.get_default_currency_code(),
                        'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Money Out to @" . @$item->details->receiver_email,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'total_charge' => getAmount(0,2).' '.get_default_currency_code(),
                        'payable' => getAmount(@$item->request_amount,2).' '.get_default_currency_code(),
                        'recipient_received' => getAmount(@$item->details->recipient_amount,2).' '.get_default_currency_code(),
                        'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                    ];

                }

        });
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
                'rate' => getAmount($data->currency->rate,4),
            ];
        })->first();
        $data =[
            'base_curr' => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'moneyOutCharge'=> (object)$moneyOutCharge,
            'userWallet'=>  (object)$userWallet,
            'transactions'   => $transactions,
        ];
        $message =  ['success'=>[__('Money Out Information')]];
        return Helpers::success($data,$message);
    }
    public function checkAgent(Request $request){
        $validator = Validator::make(request()->all(), [
            'email'     => "required|email",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $email = $request->email;
        $exist = Agent::where('email',$email)->active()->first();
        if( !$exist){
            $error = ['error'=>[__("Agent doesn't exists.")]];
            return Helpers::error($error);
        }

        $user = authGuardApi()['user'];
        if(@$exist && $user->email == @$exist->email){
            $error = ['error'=>[__("Can't money out to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'agent_email'   => $exist->email,
        ];
        $message =  ['success'=>[__('Valid agent for transaction.')]];
        return Helpers::success($data,$message);
    }
    public function qrScan(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'qr_code'     => "required|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $qr_code = $request->qr_code;
        $qrCode = AgentQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            $error = ['error'=>[__('Not found')]];
            return Helpers::error($error);
        }
        $user = Agent::where('id',$qrCode->agent_id)->active()->first();
        if(!$user){
            $error = ['error'=>[__("Agent doesn't exists.")]];
            return Helpers::error($error);
        }
        if( $user->email == auth()->user()->email){
            $error = ['error'=>[__("Can't money out to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'agent_email'   => $user->email,
            ];
        $message =  ['success'=>[__('QR Scan Result.')]];
        return Helpers::success($data,$message);
    }
    public function confirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'amount' => 'required|numeric|gt:0',
            'email' => 'required|email'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $basic_setting = BasicSettings::first();
        $sender_wallet = UserWallet::auth()->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);

        }
        if( $sender_wallet->user->email == $validated['email']){
            $error = ['error'=>[__("Can't Money out to your own")]];
            return Helpers::error($error);
        }
        $field_name = "username";
        if(check_email($validated['email'])) {
            $field_name = "email";
        }
        $receiver = Agent::where($field_name,$validated['email'])->active()->first();
        if(!$receiver){
            $error = ['error'=>[__("Receiver doesn't exists or Receiver is temporary banned")]];
            return Helpers::error($error);
        }

        $receiver_wallet = AgentWallet::where("agent_id",$receiver->id)->first();
        if(!$receiver_wallet){
            $error = ['error'=>[__('Receiver wallet not found')]];
            return Helpers::error($error);
        }

        $trx_charges =  TransactionSetting::where('slug','money-out')->where('status',1)->first();
        $charges = $this->moneyOutCharge($validated['amount'],$trx_charges,$sender_wallet,$receiver->wallet->currency);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount){
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $sender_wallet->balance){
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }

        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender($trx_id,$sender_wallet,$charges,$receiver_wallet);
            if($sender){
                 $this->insertSenderCharges($sender,$charges,$sender_wallet,$receiver_wallet);
                try{
                    if( $basic_setting->email_notification == true){
                        $notifyDataSender = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money Out to")." @" . @$receiver_wallet->agent->username." (".@$receiver_wallet->agent->email.")",
                            'request_amount'  => getAmount($charges['sender_amount'],4).' '.$charges['sender_currency'],
                            'payable'   =>  getAmount($charges['payable'],4).' ' .$charges['sender_currency'],
                            'charges'   => getAmount( $charges['total_charge'], 2).' ' .$charges['sender_currency'],
                            'received_amount'  => getAmount($charges['receiver_amount'], 2).' ' .$charges['receiver_currency'],
                            'status'  => __("success"),
                        ];
                        //sender notifications
                        $sender_wallet->user->notify(new SenderMail($sender_wallet->user,(object)$notifyDataSender));
                    }
                }catch(Exception $e){}
            }

            $receiverTrans = $this->insertReceiver($trx_id, $sender_wallet,$charges,$receiver_wallet);
            if($receiverTrans){
                 $this->insertReceiverCharges($receiverTrans,$charges,$sender_wallet,$receiver_wallet);
                 //Receiver notifications
                try{
                    if( $basic_setting->email_notification == true){
                        $notifyDataReceiver = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money Out From")." @" .@$sender_wallet->user->username." (".@$sender_wallet->user->email.")",
                            'received_amount'  => getAmount($charges['receiver_amount'], 2).' ' .$charges['receiver_currency'],
                            'status'  => __("success"),
                        ];
                        //send notifications
                        $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
                    }
                }catch(Exception $e){}

            }
             //admin notification
             $this->adminNotification($trx_id,$charges,$sender_wallet,$receiver_wallet);
            $message =  ['success'=>[__('Money Out Successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //sender transaction
     public function insertSender($trx_id,$sender_wallet,$charges,$receiver_wallet) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']) + $charges['agent_total_commission'];

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $sender_wallet->user->id,
                'user_wallet_id'                => $sender_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::AGENTMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::AGENTMONEYOUT," ")) . " To " .$receiver_wallet->agent->fullname,
                'details'                       => json_encode([
                                                        'receiver_username'=> $receiver_wallet->agent->username,
                                                        'receiver_email'=> $receiver_wallet->agent->email,
                                                        'sender_username'=> $sender_wallet->user->username,
                                                        'sender_email'=> $sender_wallet->user->email,
                                                        'charges' => $charges
                                                    ]),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function agentProfitInsert($id,$receiverWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $receiverWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges['agent_percent_commission'],
                'fixed_charge'      => $charges['agent_fixed_commission'],
                'total_charge'      => $charges['agent_total_commission'],
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$sender_wallet,$receiver_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges['percent_charge'],
                'fixed_charge'      => $charges['fixed_charge'],
                'total_charge'      => $charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         =>__("Money Out"),
                'message'       => "Money Out To  ".$receiver_wallet->agent->fullname.' ' .$charges['sender_amount'].' '.$charges['sender_currency']." Successful",
                'image'         =>  get_image($sender_wallet->user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::AGENTMONEYOUT,
                'user_id'  => $sender_wallet->user->id,
                'message'   => $notification_content,
            ]);
             //Push Notification
             (new PushNotificationHelper())->prepareApi([$sender_wallet->user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();


        }catch(Exception $e) {

            DB::rollBack();
             $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$sender_wallet,$charges,$receiver_wallet) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance +  $charges['receiver_amount']) + $charges['agent_total_commission'];


        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                       => $receiver_wallet->agent->id,
                'agent_wallet_id'                => $receiver_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::AGENTMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['receiver_amount'],
                'payable'                       => $charges['receiver_amount'],
                'available_balance'             => $receiver_wallet->balance + $charges['receiver_amount'],
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::AGENTMONEYOUT," ")) . " From " .$sender_wallet->user->fullname,
                'details'                       => json_encode([
                                                            'receiver_username'=> $receiver_wallet->agent->username,
                                                            'receiver_email'=> $receiver_wallet->agent->email,
                                                            'sender_username'=> $sender_wallet->user->username,
                                                            'sender_email'=> $sender_wallet->user->email,
                                                            'charges' => $charges
                                                        ]),
                'attribute'                     =>PaymentGatewayConst::RECEIVED,
                'status'                        => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);
            $this->agentProfitInsert($id,$receiverWallet,$charges);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
             $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($id,$charges,$sender_wallet,$receiver_wallet) {
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
                'title'         =>__("Money Out"),
                'message'       => "Money Out From ".$sender_wallet->user->fullname.' ' .$charges['receiver_amount'].' '.$charges['receiver_currency']." Successful",
                'image'         => get_image($receiver_wallet->agent->image,'agent-profile'),
            ];
            AgentNotification::create([
                'type'      => NotificationConst::AGENTMONEYOUT,
                'agent_id'   => $receiver_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notification

            (new PushNotificationHelper())->prepareApi([$receiver_wallet->agent->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'agent',
            ])->send();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function moneyOutCharge($sender_amount,$charges,$sender_wallet,$receiver_currency) {
        $exchange_rate = $receiver_currency->rate / $sender_wallet->currency->rate;

        $data['exchange_rate']                      = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['receiver_amount']                    = $sender_amount * $exchange_rate;
        $data['receiver_currency']                  = $receiver_currency->code;
        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['payable']                            = $sender_amount + $data['total_charge'];
        $data['agent_percent_commission']           = ($sender_amount / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']             = $sender_wallet->currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']             = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        return $data;
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$sender_wallet,$receiver_wallet){
        $notification_content = [
            //email notification
            'subject' => __("Money Out to")." @" . @$receiver_wallet->agent->username." (".@$receiver_wallet->agent->email.")",
            'greeting' =>__("Money Out Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$sender_wallet->user->email."<br>".__("Receiver").": @".$receiver_wallet->agent->email."<br>".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'])."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['total_charge'],$charges['sender_currency'])."<br>".__("Recipient Received")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'])."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Money Out")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$sender_wallet->user->email." ".__("Receiver").": @".$receiver_wallet->agent->email." ".__("Sender Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'])." ".__("Receiver Amount")." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency']),

            //admin db notification
            'notification_type' =>  NotificationConst::AGENTMONEYOUT,
            'admin_db_title' => "Money Out"." (".userGuard()['type'].")",
            'admin_db_message' =>"Sender".": @".$sender_wallet->user->email.","."Receiver".": @".$receiver_wallet->agent->email.","."Sender Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency']).","."Receiver Amount"." : ".get_amount($charges['receiver_amount'],$charges['receiver_currency'])
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.agent.money.out.index','admin.agent.money.out.export.data'])
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
