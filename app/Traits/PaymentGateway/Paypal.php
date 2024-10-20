<?php

namespace App\Traits\PaymentGateway;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\TemporaryData;
use App\Models\UserNotification;
use App\Notifications\User\AddMoney\ApprovedMail;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use App\Traits\TransactionAgent;
use App\Traits\PayLink\TransactionTrait;

trait Paypal
{
    use TransactionAgent,TransactionTrait;
    public function paypalInit($output = null) {
        if(!$output) $output = $this->output;
        $credentials = $this->getPaypalCredentials($output);
        if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
            return  $this->setupPaypalInitAddMoney($output,$credentials);
         }else{
             return  $this->setupPaypalInitPayLink($output,$credentials);
         }

    }
    public function setupPaypalInitAddMoney($output,$credentials){
        $config = $this->paypalConfig($credentials,$output['amount']);
        $paypalProvider = new PayPalClient;
        $paypalProvider->setApiCredentials($config);
        $paypalProvider->getAccessToken();

        if(userGuard()['guard'] == 'web'){
            $return_url = route('user.add.money.payment.success',PaymentGatewayConst::PAYPAL);
            $cancel_url = route('user.add.money.payment.cancel',PaymentGatewayConst::PAYPAL);
        }elseif(userGuard()['guard'] == 'agent'){
            $return_url = route('agent.add.money.payment.success',PaymentGatewayConst::PAYPAL);
            $cancel_url = route('agent.add.money.payment.cancel',PaymentGatewayConst::PAYPAL);
        }

        $response = $paypalProvider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" =>  $return_url,
                "cancel_url" =>  $cancel_url,
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => $output['amount']->sender_cur_code ?? '',
                        "value" => $output['amount']->total_amount ? number_format($output['amount']->total_amount,2,'.','') : 0,
                    ]
                ]
            ]
        ]);

        if(isset($response['id']) && $response['id'] != "" && isset($response['status']) && $response['status'] == "CREATED" && isset($response['links']) && is_array($response['links'])) {
            foreach($response['links'] as $item) {
                if($item['rel'] == "approve") {
                    $this->paypalJunkInsert($response);
                    return redirect()->away($item['href']);
                    break;
                }
            }
        }

        if(isset($response['error']) && is_array($response['error'])) {
            throw new Exception($response['error']['message']);
        }

        throw new Exception(__("Something went wrong! Please try again."));

    }
    public function setupPaypalInitPayLink($output,$credentials){
        $config = $this->paypalConfigPayLink($credentials,$output['charge_calculation']);
        $paypalProvider = new PayPalClient;
        $paypalProvider->setApiCredentials($config);
        $paypalProvider->getAccessToken();

        $return_url = route('payment-link.gateway.payment.paypal.success',PaymentGatewayConst::PAYPAL);
        $cancel_url = route('payment-link.gateway.payment.paypal.cancel',PaymentGatewayConst::PAYPAL);


        $response = $paypalProvider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" =>  $return_url,
                "cancel_url" =>  $cancel_url,
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => $output['charge_calculation']['sender_cur_code'] ?? 'USD',
                        "value" =>$output['charge_calculation']['requested_amount'] ? number_format($output['charge_calculation']['requested_amount'],2,'.','') : 0,
                    ]
                ]
            ]
        ]);

        if(isset($response['id']) && $response['id'] != "" && isset($response['status']) && $response['status'] == "CREATED" && isset($response['links']) && is_array($response['links'])) {
            foreach($response['links'] as $item) {
                if($item['rel'] == "approve") {
                    $this->paypalJunkInsertPayLink($response);
                    return redirect()->away($item['href']);
                    break;
                }
            }
        }

        if(isset($response['error']) && is_array($response['error'])) {
            throw new Exception($response['error']['message']);
        }

        throw new Exception(__("Something went wrong! Please try again."));

    }
    public function paypalInitApi($output = null) {
        if(!$output) $output = $this->output;
        $credentials = $this->getPaypalCredentials($output);

        $config = $this->paypalConfig($credentials,$output['amount']);
        $paypalProvider = new PayPalClient;
        $paypalProvider->setApiCredentials($config);
        $paypalProvider->getAccessToken();

        if(authGuardApi()['guard'] == 'agent_api'){
            $return_url = route('agent.api.payment.success',PaymentGatewayConst::PAYPAL."?r-source=".PaymentGatewayConst::APP);
            $cancel_url = route('agent.api.payment.cancel',PaymentGatewayConst::PAYPAL."?r-source=".PaymentGatewayConst::APP);
        }elseif(auth()->guard(get_auth_guard())->check()){
            $return_url = route('api.payment.success',PaymentGatewayConst::PAYPAL."?r-source=".PaymentGatewayConst::APP);
            $cancel_url = route('api.payment.cancel',PaymentGatewayConst::PAYPAL."?r-source=".PaymentGatewayConst::APP);
        }

        $response = $paypalProvider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" =>  $return_url,
                "cancel_url" =>  $cancel_url,
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => $output['amount']->sender_cur_code ?? '',
                        "value" => $output['amount']->total_amount ? number_format($output['amount']->total_amount,2,'.','') : 0,
                    ]
                ]
            ]
        ]);

        if(isset($response['id']) && $response['id'] != "" && isset($response['status']) && $response['status'] == "CREATED" && isset($response['links']) && is_array($response['links'])) {
            foreach($response['links'] as $item) {
                if($item['rel'] == "approve") {
                    $this->paypalJunkInsert($response);
                    return $response;
                    break;
                }
            }
        }

        if(isset($response['error']) && is_array($response['error'])) {
            throw new Exception($response['error']['message']);
        }

        throw new Exception(__("Something went wrong! Please try again."));
    }

    public function getPaypalCredentials($output) {
        $gateway = $output['gateway'] ?? null;
        if(!$gateway) throw new Exception(__("Payment gateway not available"));
        $client_id_sample = ['api key','api_key','client id','primary key'];
        $client_secret_sample = ['client_secret','client secret','secret','secret key','secret id'];
        $client_id = '';
        $outer_break = false;
        foreach($client_id_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->paypalPlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->paypalPlainText($label);
                if($label == $modify_item) {
                    $client_id = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }
        $secret_id = '';
        $outer_break = false;
        foreach($client_secret_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->paypalPlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->paypalPlainText($label);
                if($label == $modify_item) {
                    $secret_id = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }
        $mode = $gateway->env;

        $paypal_register_mode = [
            PaymentGatewayConst::ENV_SANDBOX => "sandbox",
            PaymentGatewayConst::ENV_PRODUCTION => "live",
        ];
        if(array_key_exists($mode,$paypal_register_mode)) {
            $mode = $paypal_register_mode[$mode];
        }else {
            $mode = "sandbox";
        }
        return (object) [
            'client_id'     => $client_id,
            'client_secret' => $secret_id,
            'mode'          => $mode,
        ];
    }

    public function paypalPlainText($string) {
        $string = Str::lower($string);
        return preg_replace("/[^A-Za-z0-9]/","",$string);
    }


    public static function paypalConfig($credentials, $amount_info)
    {
        $config = [
            'mode'    => $credentials->mode ?? 'sandbox',
            'sandbox' => [
                'client_id'         => $credentials->client_id ?? "",
                'client_secret'     => $credentials->client_secret ?? "",
                'app_id'            => "APP-80W284485P519543T",
            ],
            'live' => [
                'client_id'         => $credentials->client_id ?? "",
                'client_secret'     => $credentials->client_secret ?? "",
                'app_id'            => "",
            ],
            'payment_action' => 'Sale', // Can only be 'Sale', 'Authorization' or 'Order'
            'currency'       => $amount_info->sender_cur_code ?? "",
            'notify_url'     => "", // Change this accordingly for your application.
            'locale'         => 'en_US', // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
            'validate_ssl'   => true, // Validate SSL when creating api client.
        ];
        return $config;
    }
    public static function paypalConfigPayLink($credentials, $amount_info)
    {
        $config = [
            'mode'    => $credentials->mode ?? 'sandbox',
            'sandbox' => [
                'client_id'         => $credentials->client_id ?? "",
                'client_secret'     => $credentials->client_secret ?? "",
                'app_id'            => "APP-80W284485P519543T",
            ],
            'live' => [
                'client_id'         => $credentials->client_id ?? "",
                'client_secret'     => $credentials->client_secret ?? "",
                'app_id'            => "",
            ],
            'payment_action' => 'Sale', // Can only be 'Sale', 'Authorization' or 'Order'
            'currency'       => $amount_info['sender_cur_code'] ?? "",
            'notify_url'     => "", // Change this accordingly for your application.
            'locale'         => 'en_US', // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
            'validate_ssl'   => true, // Validate SSL when creating api client.
        ];
        return $config;
    }

    public function paypalJunkInsert($response) {

        $output = $this->output;
        if(authGuardApi()['type']  == "AGENT"){
            $creator_table = authGuardApi()['user']->getTable();
            $creator_id = authGuardApi()['user']->id;
            $creator_guard = authGuardApi()['guard'];
            $wallet_table = $output['wallet']->getTable();
            $wallet_id = $output['wallet']->id;
        }else{
            $creator_table = auth()->guard(get_auth_guard())->user()->getTable();
            $creator_id = auth()->guard(get_auth_guard())->user()->id;
            $creator_guard = get_auth_guard();
            $wallet_table = $output['wallet']->getTable();
            $wallet_id = $output['wallet']->id;
        }

        $data = [
            'gateway'   => $output['gateway']->id,
            'currency'  => $output['currency']->id,
            'amount'    => json_decode(json_encode($output['amount']),true),
            'response'  => $response,
            'wallet_table'  => $wallet_table,
            'wallet_id'     => $wallet_id,
            'creator_table' => $creator_table,
            'creator_id'    => $creator_id,
            'creator_guard' => $creator_guard,
        ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::PAYPAL,
            'identifier'    => $response['id'],
            'data'          => $data,
        ]);
    }
    public function paypalJunkInsertPayLink($response) {

        $output = $this->output;
        $wallet_table = $output['wallet']->getTable();
        $wallet_id = $output['wallet']->id;
        $user_relation_name = strtolower($output['user_type'])??'user';

        $data = [
            'type'                  => $output['type'],
            'gateway'               => $output['gateway']->id,
            'currency'              => $output['currency']->id,
            'validated'             => $output['validated'],
            'charge_calculation'    => json_decode(json_encode($output['charge_calculation']),true),
            'response'              => $response,
            'wallet_table'          => $wallet_table,
            'wallet_id'             => $wallet_id,
            'creator_guard'         => $output['user_guard']??'',
            'user_type'             => $output['user_type']??'',
            'user_id'               => $output['wallet']->$user_relation_name->id??'',
        ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::PAYPAL,
            'identifier'    => $response['id'],
            'data'          => $data,
        ]);
    }

    public function paypalSuccess($output = null) {
        if(!$output) $output = $this->output;
        $token = $this->output['tempData']['identifier'] ?? "";
        $credentials = $this->getPaypalCredentials($output);
        if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
            $config = $this->paypalConfig($credentials,$output['amount']);
        }else{
            $config = $this->paypalConfigPayLink($credentials,$output['charge_calculation']);
        }

        $paypalProvider = new PayPalClient;
        $paypalProvider->setApiCredentials($config);
        $paypalProvider->getAccessToken();
        $response = $paypalProvider->capturePaymentOrder($token);
        if(isset($response['status']) && $response['status'] == 'COMPLETED') {
            return $this->paypalPaymentCaptured($response,$output);
        }else {
            throw new Exception(__('Transaction failed. Payment captured failed.'));
        }

        if(empty($token)) throw new Exception(__("Transaction Failed. The record didn't save properly. Please try again"));
    }

    public function paypalPaymentCaptured($response,$output) {
        // payment successfully captured record saved to database
        $output['capture'] = $response;
        $basic_setting = BasicSettings::first();
        try{
            $trx_id = 'AM'.getTrxNum();
            $user = auth()->user();
            if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
                if(userGuard()['type'] == "USER"){
                    $this->createTransaction($output, $trx_id);
                }else{
                    return $this->createTransactionChildRecords($output,PaymentGatewayConst::STATUSSUCCESS);
                }
            }else{
                return $this->createTransactionPayLink($output,PaymentGatewayConst::STATUSSUCCESS);
            }

            if($this->requestIsApiUser()) {
                $api_user_login_guard = $this->output['api_login_guard'] ?? null;
                if( $api_user_login_guard != null ){
                    $user = auth()->guard($api_user_login_guard)->user();
                    try{
                        if( $basic_setting->email_notification == true){
                            $user->notify(new ApprovedMail($user,$output, $trx_id));
                        }
                    }catch(Exception $e){

                    }
                }
            }else{
                try{
                    if( $basic_setting->email_notification == true){

                        $user->notify(new ApprovedMail($user,$output, $trx_id));
                    }
                }catch(Exception $e){}
            }


        }catch(Exception $e) {
            throw new Exception(__("Something went wrong! Please try again."));
        }

        return true;
    }

    public function createTransaction($output, $trx_id) {
        $trx_id =  $trx_id;
        $inserted_id = $this->insertRecord($output, $trx_id);
        $this->insertCharges($output,$inserted_id);
        $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSSUCCESS);
        $this->insertDevice($output,$inserted_id);
        $this->removeTempData($output);
        if($this->requestIsApiUser()) {
            // logout user
            $api_user_login_guard = $this->output['api_login_guard'] ?? null;
            if($api_user_login_guard != null) {
                auth()->guard($api_user_login_guard)->logout();
            }
        }

    }

    public function insertRecord($output, $trx_id) {
        $trx_id =  $trx_id;
        $token = $this->output['tempData']['identifier'] ?? "";
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => auth()->user()->id,
                'user_wallet_id'                => $output['wallet']->id,
                'payment_gateway_currency_id'   => $output['currency']->id,
                'type'                          => $output['type'],
                'trx_id'                        => $trx_id,
                'request_amount'                => $output['amount']->requested_amount,
                'payable'                       => $output['amount']->total_amount,
                'available_balance'             => $output['wallet']->balance + $output['amount']->requested_amount,
                'remark'                        => ucwords(remove_speacial_char($output['type']," ")) . " With " . $output['gateway']->name,
                'details'                       => json_encode($output['capture']),
                'status'                        => true,
                'attribute'                      =>PaymentGatewayConst::SEND,
                'created_at'                    => now(),
            ]);

            $this->updateWalletBalance($output);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }

    public function updateWalletBalance($output) {
        $update_amount = $output['wallet']->balance + $output['amount']->requested_amount;

        $output['wallet']->update([
            'balance'   => $update_amount,
        ]);
    }

    public function insertCharges($output,$id) {
        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['amount']->percent_charge,
                'fixed_charge'      => $output['amount']->fixed_charge,
                'total_charge'      => $output['amount']->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => __("Your Wallet")." (".$output['wallet']->currency->code.")  ".__("balance  has been added")." ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'user_id'  =>  auth()->user()->id,
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

    public function insertDevice($output,$id) {
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

    public function removeTempData($output) {
        $token = $output['capture']['id'];
        TemporaryData::where("identifier",$token)->delete();
    }
}
