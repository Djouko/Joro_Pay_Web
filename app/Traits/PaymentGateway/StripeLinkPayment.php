<?php
namespace App\Traits\PaymentGateway;

use App\Constants\NotificationConst;
use Exception;
use Stripe\Charge;
use Stripe\Customer;
use App\Traits\Transaction;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe as StripePackage;
use App\Constants\PaymentGatewayConst;
use App\Events\Merchant\NotificationEvent;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\UserNotification as ModelsUserNotification;
use App\Notifications\PaymentLink\BuyerNotification;
use App\Notifications\PaymentLink\UserNotification;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Jenssegers\Agent\Agent;
use App\Models\Merchants\MerchantNotification;
use App\Notifications\Admin\ActivityNotification;

trait StripeLinkPayment{

    use Transaction;

    public function stripeLinkInit($output = null, $credentials) {
        if(!$output) $output = $this->output;

        StripePackage::setApiKey($credentials->secret_key);
        $cents = round($output['charge_calculation']['requested_amount'], 2) * 100;
        try {
            if($output['transaction_type'] == PaymentGatewayConst::TYPEPAYLINK){
                $type = 'payment_link';
                $trx_id = generateTrxString('transactions', 'trx_id', 'PL-', 8);
            }
            if($output['userType'] == "USER"){
                $user = $output[$type]->user;
            }else if($output['userType'] == "MERCHANT"){
                $user = $output[$type]->merchant;
            }
            // Customer Create
            $customer = Customer::create(array(
                "email"  => $output['email'],
                "name"   => $output['card_name'],
                "source" => $output['token'],
            ));

            // Charge Create
            $charge = Charge::create ([
                "amount" => $cents,
                "currency" => $output['charge_calculation']['sender_cur_code'],
                "customer" => $customer->id,
                "description" => $output[$type]['title'],
            ]);

            if ($charge['status'] == 'succeeded') {
                $this->createTransactionStripeLink($output,$trx_id,$user);

            $buyer = [
                'email' => $output['email'],
                'name'  => $output['card_name'],
            ];
            $basic_settings = BasicSettingsProvider::get();
            if($basic_settings->email_notification == true){


                try{
                    $user->notify(new UserNotification($user, $output, $trx_id));
                    Notification::route('mail', $buyer['email'])->notify(new BuyerNotification($buyer, $output, $trx_id));
                }catch(Exception $e){

                }
            }

               return true;
            }
        } catch (\Exception $e) {
            throw new Exception(__("Something went wrong! Please try again."));
        }


    }

    public function createTransactionStripeLink($output,$trx_id,$user) {
        $trx_id =  $trx_id;
        try {
            $this->adminNotificationPayLinkCardPayment($trx_id,$output,$status = PaymentGatewayConst::STATUSSUCCESS,$user);
            $inserted_id = $this->insertRecordStripeLink($output,$trx_id);
            $this->insertChargesStripeLink($inserted_id, $output);
            $this->insertDeviceStripe($output,$inserted_id);
            return true;
        } catch (\Exception $e) {
            throw new Exception(__("Something went wrong! Please try again."));
        }

    }
    public function insertRecordStripeLink($output, $trx_id) {
        $trx_id = $trx_id;
        $type = 'payment_link';
        $output['payment_type'] = PaymentGatewayConst::TYPE_CARD_PAYMENT;
        DB::beginTransaction();
        try{
            if($output['userType'] == "USER"){
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                     => $output['receiver_wallet']->user_id,
                    'user_wallet_id'              => $output['receiver_wallet']->id,
                    'payment_link_id'             => $output[$type]->id,
                    'payment_gateway_currency_id' => NULL,
                    'type'                        => $output['transaction_type'],
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['payable'],
                    'available_balance'           => $output['receiver_wallet']->balance + $output['charge_calculation']['conversion_payable'],
                    'remark'                      => ucwords($output['transaction_type']." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'status'                      => true,
                    'attribute'                   => PaymentGatewayConst::RECEIVED,
                    'created_at'                  => now(),
                ]);
            }else if($output['userType'] == "MERCHANT"){
                $id = DB::table("transactions")->insertGetId([
                    'merchant_id'                 => $output['receiver_wallet']->merchant_id,
                    'merchant_wallet_id'          => $output['receiver_wallet']->id,
                    'payment_link_id'             => $output[$type]->id,
                    'payment_gateway_currency_id' => NULL,
                    'type'                        => $output['transaction_type'],
                    'trx_id'                      => $trx_id,
                    'request_amount'              => $output['charge_calculation']['requested_amount'],
                    'payable'                     => $output['charge_calculation']['payable'],
                    'available_balance'           => $output['receiver_wallet']->balance + $output['charge_calculation']['conversion_payable'],
                    'remark'                      => ucwords($output['transaction_type']." Transaction Successfully"),
                    'details'                     => json_encode($output),
                    'status'                      => true,
                    'attribute'                   => PaymentGatewayConst::RECEIVED,
                    'created_at'                  => now(),
                ]);
            }

            $this->updateWalletBalanceStripeLink($output);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function insertChargesStripeLink($id,$output) {
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
            if($output['userType'] == "USER"){
                $this->notificationUser($output);
            }else if($output['userType'] == "MERCHANT"){
                $this->notificationMerchant($output);
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function notificationUser($output){
        $user = $output['receiver_wallet']->user;
         //notification
         $notification_content = [
            'title'         => __("Payment From PayLink"),
            'message'       => __("Your Wallet")." (".$output['receiver_wallet']->currency->code.") ".__("balance  has been added").' '.$output['charge_calculation']['conversion_payable'].' '. $output['receiver_wallet']->currency->code,
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
        $user = $output['receiver_wallet']->merchant;
         //notification
         $notification_content = [
            'title'         => "Payment From PayLink",
            'message'       => "Your Wallet"." (".$output['receiver_wallet']->currency->code.") "."balance  has been added".' '.$output['charge_calculation']['conversion_payable'].' '. $output['receiver_wallet']->currency->code,
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
    public function insertDeviceStripe($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();
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
    public function updateWalletBalanceStripeLink($output) {
        $update_amount = $output['receiver_wallet']->balance + $output['charge_calculation']['conversion_payable'];
        $output['receiver_wallet']->update([
            'balance'   => $update_amount,
        ]);
    }
    public function adminNotificationPayLinkCardPayment($trx_id,$output,$status,$user){
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
            'subject' =>__('pay Link')." (".$output['userType'].")",
            'greeting' =>__("Payment Received Via")." ".__("Card Payment"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($output['charge_calculation']['requested_amount'],$output['charge_calculation']['sender_cur_code'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($output['charge_calculation']['total_charge'],$output['charge_calculation']['sender_cur_code'])."<br>".__("Total Payable Amount")." : ".get_amount($output['charge_calculation']['conversion_payable'],$output['charge_calculation']['receiver_currency_code'])."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __('pay Link')." (".$output['userType'].")",
            'push_content' => __('web_trx_id').": ".$trx_id.",".__("Balance Added To Wallet") ." (".$output['receiver_wallet']->currency->code.") ".get_amount($output['charge_calculation']['conversion_payable'],$output['receiver_wallet']->currency->code) .' '.'by'.','.__("Card Payment").',('.$user->username.')',
            //admin db notification
            'notification_type' =>  NotificationConst::PAY_LINK,
            'trx_id' =>  $trx_id,
            'admin_db_title' =>   'Pay Link'." (".$output['userType'].")",
            'admin_db_message' => 'Transaction ID'.": ".$trx_id.","."Balance Added To Wallet" ." (".$output['receiver_wallet']->currency->code.") ".get_amount($output['charge_calculation']['conversion_payable'],$output['receiver_wallet']->currency->code) .' by '.__("Card Payment").',('.$user->username.')'
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
