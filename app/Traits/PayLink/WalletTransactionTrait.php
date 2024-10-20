<?php

namespace App\Traits\PayLink;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Merchants\MerchantNotification;
use App\Models\UserNotification as ModelsUserNotification;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\PaymentLink\Wallet\UserNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Notifications\PaymentLink\Wallet\BuyerNotification;

trait WalletTransactionTrait {
    public function createWalletTransactionPayLink($output,$status = PaymentGatewayConst::STATUSSUCCESS) {
        $basic_setting = BasicSettings::first();
        $receiver_user  = $output['receiver'];
        $sender_user    = $output['sender'];
        $trx_id = generateTrxString('transactions', 'trx_id', 'PL-', 8);

        if($output['user_type'] ===  "MERCHANT"){
            $email_notification_status = $basic_setting->merchant_email_notification;
        }else{
            $email_notification_status = $basic_setting->email_notification;
        }

        $inserted_id = $this->insertRecordPayLinkWallet($output,$trx_id,$status);
        $this->transactionChargePayLink($inserted_id,$output);
        $this->adminNotificationPayLink($trx_id,$output,$status,$receiver_user);

        try{
             //receiver notification
            if($email_notification_status == true){
                $receiver_user->notify(new UserNotification($receiver_user,$output,$trx_id));
            }
            //sender(user) notification
            if($basic_setting->email_notification == true){
                $sender_user->notify(new BuyerNotification($sender_user,$output,$trx_id));
            }
        }catch (\Exception $e) {}

    }

    public function insertRecordPayLinkWallet($output,$trx_id,$status){
        $receiver_available_balance = $output['receiver_wallet']->balance + $output['charge_calculation']['conversion_payable'];
        $sender_available_balance = $output['sender_wallet']->balance - $output['charge_calculation']['sender_payable'];

        DB::beginTransaction();
        try{
            //receiver transactions
            if($output['user_type'] == "USER"){
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                     => $output['receiver_wallet']->user_id,
                    'user_wallet_id'              => $output['receiver_wallet']->id,
                    'payment_link_id'             => $output['payment_link']->id,
                    'payment_gateway_currency_id' => null,
                    'type'                        => PaymentGatewayConst::TYPEPAYLINK,
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['payable'],
                    'available_balance'           => $receiver_available_balance,
                    'remark'                      => ucwords(PaymentGatewayConst::TYPEPAYLINK." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'status'                      => $status,
                    'attribute'                   => PaymentGatewayConst::RECEIVED,
                    'created_at'                  => now(),
                ]);
            }else if($output['user_type'] == "MERCHANT"){
                $id = DB::table("transactions")->insertGetId([
                    'merchant_id'                 => $output['receiver_wallet']->merchant_id,
                    'merchant_wallet_id'          => $output['receiver_wallet']->id,
                    'payment_link_id'             => $output['payment_link']->id,
                    'payment_gateway_currency_id' => null,
                    'type'                        => PaymentGatewayConst::TYPEPAYLINK,
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['payable'],
                    'available_balance'           => $receiver_available_balance,
                    'remark'                      => ucwords(PaymentGatewayConst::TYPEPAYLINK." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'status'                      => $status,
                    'attribute'                   => PaymentGatewayConst::RECEIVED,
                    'created_at'                  => now(),
                ]);
            }
            //receiver transactions end
            //sender transactions start
                $sender_id = DB::table("transactions")->insertGetId([
                    'user_id'                     => $output['sender_wallet']->user_id,
                    'user_wallet_id'              => $output['sender_wallet']->id,
                    'payment_link_id'             => $output['payment_link']->id,
                    'payment_gateway_currency_id' => null,
                    'type'                        => PaymentGatewayConst::TYPEPAYLINK,
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['sender_payable'],
                    'available_balance'           => $sender_available_balance,
                    'remark'                      => ucwords(PaymentGatewayConst::TYPEPAYLINK." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'status'                      => $status,
                    'attribute'                   => PaymentGatewayConst::SEND,
                    'created_at'                  => now(),
                ]);
            $this->transactionChargePayLinkSender($sender_id,$output);
            //sender transactions end
            $this->updateWalletBalancePayLink($output);
            $this->updateWalletBalancePayLinkSender($output);

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
    public function updateWalletBalancePayLinkSender($output) {
        $update_amount = $output['sender_wallet']->balance - $output['charge_calculation']['sender_payable'];
        $output['sender_wallet']->update([
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
                $this->notificationUserWallet($output);
            }else if($output['user_type'] == "MERCHANT"){
                $this->notificationMerchantWallet($output);
            }
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function transactionChargePayLinkSender($id,$output) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['charge_calculation']['percent_charge'],
                'fixed_charge'      => $output['charge_calculation']['fixed_charge'],
                'total_charge'      => $output['charge_calculation']['total_charge'],
                'created_at'        => now(),
            ]);

            $this->notificationSenderUser($output);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function notificationSenderUser($output){
        $user = $output['sender'];
         //notification
         $notification_content = [
            'title'         => "PayLink Payment",
            'message'       => "Your PayLink Payment Request Send Successful"."  ".get_amount($output['charge_calculation']['sender_payable'],$output['sender_wallet']->currency->code),
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
    public function notificationUserWallet($output){
        $user_relation_name = strtolower($output['user_type'])??'user';
        $user = $output['receiver_wallet']->$user_relation_name;
         //notification
         $notification_content = [
            'title'         => "Payment From PayLink",
            'message'       => "Your Wallet"." (".$output['receiver_wallet']->currency->code.") "."balance has been added".' '.$output['charge_calculation']['conversion_payable'].' '. $output['receiver_wallet']->currency->code,
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
    public function notificationMerchantWallet($output){
        $user_relation_name = strtolower($output['user_type'])??'user';
        $user = $output['receiver_wallet']->$user_relation_name;
         //notification
         $notification_content = [
            'title'         => "Payment From PayLink",
            'message'       => "Your Wallet"." (".$output['receiver_wallet']->currency->code.") "."balance has been added".' '.$output['charge_calculation']['conversion_payable'].' '. $output['receiver_wallet']->currency->code,
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

    // //admin notification global(Merchant & User)
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
            'subject' =>__('pay Link')." (".__("rWallet").")",
            'greeting' =>__("Payment Received Via")." ".__("rWallet"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($output['charge_calculation']['requested_amount'],$output['charge_calculation']['sender_cur_code'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($output['charge_calculation']['total_charge'],$output['charge_calculation']['sender_cur_code'])."<br>".__("Total Payable Amount")." : ".get_amount($output['charge_calculation']['conversion_payable'],$output['charge_calculation']['receiver_currency_code'])."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __('pay Link')." (".$output['user_type'].")",
            'push_content' => __('web_trx_id').": ".$trx_id.",".__("Balance Added To Wallet") ." (".$output['receiver_wallet']->currency->code.") ".get_amount($output['charge_calculation']['conversion_payable'],$output['receiver_wallet']->currency->code) .' '.'by'.','.__("rWallet").',('.$user->username.')',

            //admin db notification
            'notification_type' =>  NotificationConst::PAY_LINK,
            'trx_id' =>  $trx_id,
            'admin_db_title' =>   'Pay Link'." (".$output['user_type'].")",
            'admin_db_message' => 'Transaction ID'.": ".$trx_id.","."Balance Added To Wallet" ." (".$output['receiver_wallet']->currency->code.") ".get_amount($output['charge_calculation']['conversion_payable'],$output['receiver_wallet']->currency->code) .' by '.__("rWallet").',('.$user->username.')'
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
