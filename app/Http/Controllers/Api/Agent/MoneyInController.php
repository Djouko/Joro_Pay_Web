<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\TransactionSetting;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserQrCode;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\MoneyIn\ReceiverMail as MoneyInReceiverMail;
use App\Notifications\Agent\MoneyIn\SenderMail as MoneyInSenderMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MoneyInController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'MI'.getTrxNum();
    }
    public function MoneyInInfo(){
        $user = authGuardApi()['user'];
        $moneyInCharge = TransactionSetting::where('slug','money-in')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
                'agent_fixed_commissions' => getAmount($data->agent_fixed_commissions,2),
                'agent_percent_commissions' => getAmount($data->agent_percent_commissions,2),
                'agent_profit' => $data->agent_profit,
            ];
        })->first();
        $transactions = Transaction::agentAuth()->moneyIn()->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                return[
                    'id' => @$item->id,
                    'type' =>$item->attribute,
                    'trx' => @$item->trx_id,
                    'transaction_type' => $item->type,
                    'transaction_heading' => "Money In to @" . @$item->details->receiver_email,
                    'request_amount' => getAmount(@$item->request_amount,2).' '.$item->details->charges->sender_currency,
                    'total_charge' => getAmount(@$item->charge->total_charge,2).' '.$item->details->charges->sender_currency,
                    'payable' => getAmount(@$item->payable,2).' '.$item->details->charges->sender_currency,
                    'recipient_received' => getAmount(@$item->details->charges->receiver_amount,2).' '.$item->details->charges->receiver_currency,
                    'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                    'status' => @$item->stringStatus->value ,
                    'date_time' => @$item->created_at ,
                    'status_info' =>(object)@$statusInfo ,
                ];

        });
        $agentWallet = AgentWallet::where('agent_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,4),
                'currency' => $data->currency->code,
                'rate' => getAmount($data->currency->rate,4),
            ];
        })->first();
        $data =[
            'base_curr' => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'moneyInCharge'=> (object)$moneyInCharge,
            'agentWallet'=>  (object)$agentWallet,
            'transactions'   => $transactions,
        ];
        $message =  ['success'=>[__('Money In Information')]];
        return Helpers::success($data,$message);
    }
    public function checkUser(Request $request){
        $validator = Validator::make(request()->all(), [
            'email'     => "required|email",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $column = "email";
        if(check_email($request->email)) $column = "email";
        $exist = User::where($column,$request->email)->active()->first();
        if( !$exist){
            $error = ['error'=>[__('User not found')]];
            return Helpers::error($error);
        }
        $user = authGuardApi()['user'];
        if(@$exist && $user->email == @$exist->email){
            $error = ['error'=>[__("Can't Money-In to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'receiver_email'   => $exist->email,
            'receiver_wallet' => [
                'country_name' => $exist->wallet->currency->country,
                'currency_code' => $exist->wallet->currency->code,
                'rate' => $exist->wallet->currency->rate,
                'currency_symbol' => $exist->wallet->currency->symbol,
            ],
        ];
        $message =  ['success'=>[__('Valid user for transaction.')]];
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
        $qrCode = UserQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            $error = ['error'=>[__('Invalid QR Code')]];
            return Helpers::error($error);
        }
        $user = User::where('id',$qrCode->user_id)->active()->first();
        if(!$user){
            $error = ['error'=>[__('User not found')]];
            return Helpers::error($error);
        }
        if( $user->email == auth()->user()->email){
            $error = ['error'=>[__("Can't Money-In to your own")]];
            return Helpers::error($error);
        }
        $data =[
            'receiver_email'   => $user->email,
            'receiver_wallet' => [
                'country_name' => $user->wallet->currency->country,
                'user' => $user->wallet->currency->code,
                'rate' => $user->wallet->currency->rate,
                'currency_symbol' => $user->wallet->currency->symbol,
            ],
        ];
        $message =  ['success'=>[__('QR Scan Result.')]];
        return Helpers::success($data,$message);
    }
    public function confirmedMoneyIn(Request $request){
        $validator = Validator::make(request()->all(), [
            'amount' => 'required|numeric|gt:0',
            'email' => 'required|email'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated =  $validator->validate();
        $basic_setting = BasicSettings::first();

        $sender_wallet = AgentWallet::auth()->active()->first();
        if(!$sender_wallet){
            $error = ['error'=>[__('Agent wallet not found!')]];
            return Helpers::error($error);
        }
        if( $sender_wallet->agent->email == $validated['email']){
            $error = ['error'=>[__("Can't Money-In to your own")]];
            return Helpers::error($error);
        }
        $field_name = "username";
        if(check_email($validated['email'])) {
            $field_name = "email";
        }
        $receiver = User::where($field_name,$validated['email'])->active()->first();
        if(!$receiver){
            $error = ['error'=>[__("Receiver doesn't exists or Receiver is temporary banned")]];
            return Helpers::error($error);
        }
        $receiver_wallet = UserWallet::where("user_id",$receiver->id)->first();

        if(!$receiver_wallet){
            $error = ['error'=>[__('Receiver wallet not found')]];
            return Helpers::error($error);
        }

        $trx_charges =  TransactionSetting::where('slug','money-in')->where('status',1)->first();
        $charges = $this->moneyInCharge($validated['amount'],$trx_charges,$sender_wallet,$receiver->wallet->currency);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $sender_wallet->balance) {
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
         }
        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender($trx_id,$sender_wallet,$charges,$receiver_wallet);
            if($sender){
                 $this->insertSenderCharges($sender,$charges,$sender_wallet,$receiver_wallet);
                 try{
                    if( $basic_setting->agent_email_notification == true){
                        $notifyDataSender = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money In To")." @" . @$receiver_wallet->user->username." (".@$receiver_wallet->user->email.")",
                            'request_amount'  => getAmount($charges['sender_amount'],4).' '.$charges['sender_currency'],
                            'payable'   =>  getAmount($charges['payable'],4).' ' .$charges['sender_currency'],
                            'charges'   => getAmount( $charges['total_charge'], 2).' ' .$charges['sender_currency'],
                            'received_amount'  => getAmount($charges['receiver_amount'], 2).' ' .$charges['receiver_currency'],
                            'status'  => __("success"),
                        ];
                        //sender notifications
                        $sender_wallet->agent->notify(new MoneyInSenderMail($sender_wallet->agent,(object)$notifyDataSender));
                    }
                }catch(Exception $e){
                    //Error Handle
                }
            }

            $receiverTrans = $this->insertReceiver($trx_id, $sender_wallet,$charges,$receiver_wallet);
            if($receiverTrans){
                 $this->insertReceiverCharges($receiverTrans,$charges,$sender_wallet,$receiver_wallet);
                 //Receiver notifications
                 try{
                    if( $basic_setting->agent_email_notification == true){
                        $notifyDataReceiver = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money In From")." @" .@$sender_wallet->agent->username." (".@$sender_wallet->agent->email.")",
                            'received_amount'  => getAmount($charges['receiver_amount'], 2).' ' .$charges['receiver_currency'],
                            'status'  => __("success"),
                        ];
                        //send notifications
                        $receiver->notify(new MoneyInReceiverMail($receiver,(object)$notifyDataReceiver));
                    }
                }catch(Exception $e){
                    //Error Handle
                }
            }
            //admin notification
            $this->adminNotification($trx_id,$charges,$sender_wallet->agent,$receiver);
            $message =  ['success'=>[__("Money In Request Successful")]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }

    }
     //admin notification
     public function adminNotification($trx_id,$charges,$sender,$receiver){
        $notification_content = [
            //email notification
            'subject' =>__("Money In")." (".authGuardApi()['type'].")",
            'greeting' =>__("Money In Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$sender->email."<br>".__("Receiver").": @".$receiver->email."<br>".__("request Amount")." : ".get_amount($charges['sender_amount'],get_default_currency_code())."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],get_default_currency_code())."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],get_default_currency_code())."<br>".__("Recipient Received")." : ".get_amount($charges['sender_amount'],get_default_currency_code())."<br>".__("Status")." : ".__("success"),

            //push notification
            'push_title' => __("Money In")." ".__('Successful')." (".authGuardApi()['type'].")",
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$sender->email." ".__("Receiver").": @".$receiver->email." ".__("Sender Amount")." : ".get_amount($charges['sender_amount'],get_default_currency_code())." ".__("Receiver Amount")." : ".get_amount($charges['sender_amount'],get_default_currency_code()),

            //admin db notification
            'notification_type' =>  NotificationConst::MONEYIN,
            'trx_id' =>  $trx_id,
            'admin_db_title' => "Money In"." (".$trx_id.")"." (".authGuardApi()['type'].")",
            'admin_db_message' =>"Sender".": @".$sender->email.","."Receiver".": @".$receiver->email.","."Sender Amount"." : ".get_amount($charges['sender_amount'],get_default_currency_code()).","."Receiver Amount"." : ".get_amount($charges['sender_amount'],get_default_currency_code())
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.money.in.index','admin.money.in.export.data'])
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                            'from'  => 'api',
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    //sender transaction
    public function insertSender($trx_id,$sender_wallet,$charges,$receiver_wallet) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']) + $charges['agent_total_commission'];

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $sender_wallet->agent->id,
                'agent_wallet_id'               => $sender_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MONEYIN,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MONEYIN," ")) . " To " .$receiver_wallet->user->fullname,
                'details'                       => json_encode([
                                                        'receiver_username'=> $receiver_wallet->user->username,
                                                        'receiver_email'=> $receiver_wallet->user->email,
                                                        'sender_username'=> $sender_wallet->agent->username,
                                                        'sender_email'=> $sender_wallet->agent->email,
                                                        'charges' => $charges
                                                    ]),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);
            $this->agentProfitInsert($id,$authWallet,$charges);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges['agent_percent_commission'],
                'fixed_charge'      => $charges['agent_fixed_commission'],
                'total_charge'      => $charges['agent_total_commission'],
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
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
                'title'         =>__("Money IN"),
                'message'       => "Money In To  ".$receiver_wallet->user->fullname.' ' .$charges['sender_amount'].' '.$charges['sender_currency']." Successful",
                'image'         =>  get_image($sender_wallet->agent->image,'agent-profile'),
            ];
            AgentNotification::create([
                'type'      => NotificationConst::MONEYIN,
                'agent_id'  => $sender_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            (new PushNotificationHelper())->prepareApi([$sender_wallet->agent->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'agent',
            ])->send();

        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$sender_wallet,$charges,$receiver_wallet) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges['receiver_amount']);

        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver_wallet->user->id,
                'user_wallet_id'                => $receiver_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MONEYIN,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['receiver_amount'],
                'payable'                       => $charges['receiver_amount'],
                'available_balance'             => $receiver_wallet->balance + $charges['receiver_amount'],
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MONEYIN," ")) . " From " .$sender_wallet->agent->fullname,
                'details'                       => json_encode([
                                                            'receiver_username'=> $receiver_wallet->user->username,
                                                            'receiver_email'=> $receiver_wallet->user->email,
                                                            'sender_username'=> $sender_wallet->agent->username,
                                                            'sender_email'=> $sender_wallet->agent->email,
                                                            'charges' => $charges
                                                        ]),
                'attribute'                     =>PaymentGatewayConst::RECEIVED,
                'status'                        => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
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
                'title'         =>__("Money In"),
                'message'       => "Money In From  ".$sender_wallet->agent->fullname.' ' .$charges['receiver_amount'].' '.$charges['receiver_currency']." Successful",
                'image'         => get_image($receiver_wallet->user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::MONEYIN,
                'user_id'   => $receiver_wallet->user->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            (new PushNotificationHelper())->prepareApi([$receiver_wallet->user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();

        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    public function moneyInCharge($sender_amount,$charges,$sender_wallet,$receiver_currency) {
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
}
