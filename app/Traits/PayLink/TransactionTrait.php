<?php

namespace App\Traits\PayLink;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Merchants\MerchantNotification;
use App\Models\TemporaryData;
use App\Models\UserNotification as ModelsUserNotification;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\PaymentLink\Gateway\UserNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use App\Notifications\PaymentLink\Gateway\BuyerNotification;

trait TransactionTrait {
    public function createTransactionPayLink($output,$status = PaymentGatewayConst::STATUSSUCCESS) {

        $basic_setting = BasicSettings::first();
        $user_relation_name = strtolower($output['user_type'])??'user';
        $user = $output['receiver_wallet']->$user_relation_name;
        $trx_id = generateTrxString('transactions', 'trx_id', 'PL-', 8);
        $inserted_id = $this->insertRecordPayLink($output,$trx_id,$status);
        $this->transactionChargePayLink($inserted_id,$output);
        $this->adminNotificationPayLink($trx_id,$output,$status,$user);
        $this->insertDevicePayLink($output,$inserted_id);
        // $this->removeTempDataPayLink($output);
        $buyer = [
            'email' => $output['validated']['email'],
            'name'  => $output['validated']['full_name'],
        ];

        if($basic_setting->email_notification == true){
            try {
                $user->notify(new UserNotification($user, $output, $trx_id));
                Notification::route('mail', $buyer['email'])->notify(new BuyerNotification($buyer, $output, $trx_id));
            } catch (\Exception $e) {}
        }

    }

    public function insertRecordPayLink($output,$trx_id,$status){
        $trx_id = $trx_id;
        $type = 'payment_link';
        $output['payment_type'] = PaymentGatewayConst::TYPE_GATEWAY_PAYMENT;
        if($status === PaymentGatewayConst::STATUSSUCCESS) {
            $available_balance = $output['receiver_wallet']->balance + $output['charge_calculation']['conversion_payable'];
        }else{
            $available_balance = $output['receiver_wallet']->balance;
        }

        DB::beginTransaction();
        try{
            if($output['user_type'] == "USER"){
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                     => $output['receiver_wallet']->user_id,
                    'user_wallet_id'              => $output['receiver_wallet']->id,
                    'payment_link_id'             => $output[$type]->id,
                    'payment_gateway_currency_id' => $output['currency']->id,
                    'type'                        => $output['type'],
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['payable'],
                    'available_balance'           =>  $available_balance,
                    'remark'                      => ucwords($output['type']." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'callback_ref'                => $output['callback_ref'] ?? null,
                    'status'                      => $status,
                    'attribute'                   => PaymentGatewayConst::RECEIVED,
                    'created_at'                  => now(),
                ]);
            }else if($output['user_type'] == "MERCHANT"){
                $id = DB::table("transactions")->insertGetId([
                    'merchant_id'                 => $output['receiver_wallet']->merchant_id,
                    'merchant_wallet_id'          => $output['receiver_wallet']->id,
                    'payment_link_id'             => $output[$type]->id,
                    'payment_gateway_currency_id' => $output['currency']->id,
                    'type'                        => $output['type'],
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['payable'],
                    'available_balance'           =>  $available_balance,
                    'remark'                      => ucwords($output['type']." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'callback_ref'                => $output['callback_ref'] ?? null,
                    'status'                      => $status,
                    'attribute'                   => PaymentGatewayConst::RECEIVED,
                    'created_at'                  => now(),
                ]);
            }

            if($status === PaymentGatewayConst::STATUSSUCCESS) {
                $this->updateWalletBalancePayLink($output);
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateWalletBalancePayLink($output) {
        $update_amount = $output['receiver_wallet']->balance + $output['charge_calculation']['conversion_payable'];
        $output['receiver_wallet']->update([
            'balance'   => $update_amount,
        ]);
    }
    public function transactionChargePayLink($id,$output) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['charge_calculation']['percent_charge'],
                'fixed_charge'      => $output['charge_calculation']['fixed_charge'],
                'total_charge'      => $output['charge_calculation']['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            if($output['user_type'] == "USER"){
                $this->notificationUser($output);
            }else if($output['user_type'] == "MERCHANT"){
                $this->notificationMerchant($output);
            }
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function notificationUser($output){
        $user_relation_name = strtolower($output['user_type'])??'user';
        $user = $output['receiver_wallet']->$user_relation_name;
         //notification
         $notification_content = [
            'title'         => __("Payment From PayLink")." (".$output['user_type'].")",
            'message'       => __("Your Wallet")." (".$output['receiver_wallet']->currency->code.") ".__("balance has been added").' '.$output['charge_calculation']['conversion_payable'].' '. $output['receiver_wallet']->currency->code,
            'time'          => Carbon::now()->diffForHumans(),
            'image'         => get_image($user->image,'user-profile'),
        ];

        ModelsUserNotification::create([
            'type'      => NotificationConst::PAY_LINK,
            'user_id'  => $user->id,
            'message'   => $notification_content,
        ]);

         //Push Notifications
         (new PushNotificationHelper())->prepareUnauthorize([$user->id],[
            'title' => $notification_content['title'],
            'desc'  => $notification_content['message'],
            'user_type' => 'user',
            'unauthorize'  => true,
            'user_guard'  => 'web',
        ])->send();

    }
    public function notificationMerchant($output){
        $user_relation_name = strtolower($output['user_type'])??'user';
        $user = $output['receiver_wallet']->$user_relation_name;
         //notification
         $notification_content = [
            'title'         => "Payment From PayLink",
            'message'       => "Your Wallet"." (".$output['receiver_wallet']->currency->code.") "."balance has been added".' '.$output['charge_calculation']['conversion_payable'].' '. $output['receiver_wallet']->currency->code." By ".$output['currency']->name,
            'time'          => Carbon::now()->diffForHumans(),
            'image'         => get_image($user->image,'merchant-profile'),
        ];

        MerchantNotification::create([
            'type'      => NotificationConst::PAY_LINK,
            'merchant_id'  => $user->id,
            'message'   => $notification_content,
        ]);

        //Push Notifications
        (new PushNotificationHelper())->prepareUnauthorize([$user->id],[
            'title' => $notification_content['title'],
            'desc'  => $notification_content['message'],
            'user_type' => 'merchant',
            'unauthorize'  => true,
            'user_guard'  => 'merchant',
        ])->send();
    }
    public function insertDevicePayLink($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function removeTempDataPayLink($output) {
        TemporaryData::where("identifier",$output['tempData']['identifier'])->delete();
    }
    //admin notification global(Merchant & User)
    public function adminNotificationPayLink($trx_id,$output,$status,$user){
        $exchange_rate = " 1 ". $output['charge_calculation']['sender_cur_code'].' = '. get_amount($output['charge_calculation']['exchange_rate'],$output['charge_calculation']['receiver_currency_code']);
        if($status == PaymentGatewayConst::STATUSSUCCESS){
            $status ="success";
        }elseif($status == PaymentGatewayConst::STATUSPENDING){
            $status ="Pending";
        }elseif($status == PaymentGatewayConst::STATUSHOLD){
            $status ="Hold";
        }elseif($status == PaymentGatewayConst::STATUSWAITING){
            $status ="Waiting";
        }elseif($status == PaymentGatewayConst::STATUSPROCESSING){
            $status ="Processing";
        }


        $notification_content = [
            //email notification
            'subject' =>__('pay Link')." (".$output['user_type'].")",
            'greeting' =>__("Payment Received Via")." ".$output['currency']->name,
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($output['charge_calculation']['requested_amount'],$output['charge_calculation']['sender_cur_code'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($output['charge_calculation']['total_charge'],$output['charge_calculation']['sender_cur_code'])."<br>".__("Total Payable Amount")." : ".get_amount($output['charge_calculation']['conversion_payable'],$output['charge_calculation']['receiver_currency_code'])."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __('pay Link')." (".$output['user_type'].")",
            'push_content' => __('web_trx_id').": ".$trx_id.",".__("Balance Added To Wallet") ." (".$output['receiver_wallet']->currency->code.") ".get_amount($output['charge_calculation']['conversion_payable'],$output['receiver_wallet']->currency->code) .' '.'by'.','.$output['currency']->name.',('.$user->username.')',

            //admin db notification
            'notification_type' =>  NotificationConst::PAY_LINK,
            'trx_id' =>  $trx_id,
            'admin_db_title' =>   'Pay Link'." (".$output['user_type'].")",
            'admin_db_message' => 'Transaction ID'.": ".$trx_id.","."Balance Added To Wallet" ." (".$output['receiver_wallet']->currency->code.") ".get_amount($output['charge_calculation']['conversion_payable'],$output['receiver_wallet']->currency->code) .' by '.$output['currency']->name.',('.$user->username.')'
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.payment.link.index','admin.payment.link.all.link','admin.payment.link.active.link','admin.payment.link.closed.link','admin.payment.link.details','admin.payment.link.export.data'])
                                    ->mail(ActivityNotification::class, [
                                        'subject'   => $notification_content['subject'],
                                        'greeting'  => $notification_content['greeting'],
                                        'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                        'user_type' => "admin",
                                        'title' => $notification_content['push_title'],
                                        'desc'  => $notification_content['push_content'],
                                        'unauthorize'  => true,
                                        'user_guard'  => $output['user_guard'],
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
