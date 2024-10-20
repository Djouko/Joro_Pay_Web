<?php

namespace App\Traits\PaymentGateway;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\Pagadito;
use App\Models\TemporaryData;
use App\Models\UserNotification;
use App\Notifications\User\AddMoney\ApprovedMail;
use App\Providers\Admin\BasicSettingsProvider;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use App\Http\Helpers\PushNotificationHelper;
use App\Traits\TransactionAgent;
use App\Traits\PayLink\TransactionTrait;


trait PagaditoTrait
{
    use TransactionAgent,TransactionTrait;
    public function pagaditoInit($output = null) {
        $basic_settings = BasicSettingsProvider::get();
        if(!$output) $output = $this->output;
        $credentials = $this->getPagaditoCredentials($output);
        $this->pagaditoSetSecreteKey($credentials);
        $uid = $credentials->uid;
        $wsk = $credentials->wsk;
        $mode = $credentials->mode;
        if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
            $amount = $output['amount']->total_amount;
            $currency = $output['amount']->sender_cur_code;
            $details_text = __("Add Money");
         }else{
            $amount = $output['charge_calculation']['requested_amount'];
            $currency = $output['charge_calculation']['sender_cur_code'];
            $details_text = __("Pay Link");
         }


        $Pagadito = new Pagadito($uid,$wsk,$credentials,$currency);
        $Pagadito->config( $credentials,$currency);
        $env = userGuard()['guard'];

        if ($mode == "sandbox") {
            $Pagadito->mode_sandbox_on();
        }
        if ($Pagadito->connect()) {

            $Pagadito->add_detail(1,__("Please Pay For")." ".$basic_settings->site_name." ". $details_text,$amount);
            $Pagadito->set_custom_param("param1", "Valor de param1");
            $Pagadito->set_custom_param("param2", "Valor de param2");
            $Pagadito->set_custom_param("param3", "Valor de param3");
            $Pagadito->set_custom_param("param4", "Valor de param4");
            $Pagadito->set_custom_param("param5", "Valor de param5");

            $Pagadito->enable_pending_payments();
            $getUrls = (object)$Pagadito->exec_trans($Pagadito->get_rs_code());

            if($getUrls->code == "PG1002" ){
                $parts = parse_url($getUrls->value);
                parse_str($parts['query'], $query);
                // Extract the token value
                if (isset($query['token'])) {
                    $tokenValue = $query['token'];
                } else {
                    $tokenValue = '';
                }
                if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
                    $this->pagaditoJunkInsert($getUrls,$tokenValue, $env);
                }else{
                    $this->pagaditoJunkInsertPayLink($getUrls,$tokenValue);
                }

                return redirect($getUrls->value);

            }
            $ern = rand(1000, 2000);
            if (!$Pagadito->exec_trans($ern)) {
                switch($Pagadito->get_rs_code())
                {
                    case "PG2001":
                        /*Incomplete data*/
                    case "PG3002":
                        /*Error*/
                    case "PG3003":
                        /*Unregistered transaction*/
                    case "PG3004":
                        /*Match error*/
                    case "PG3005":
                        /*Disabled connection*/
                    default:
                        throw new Exception($Pagadito->get_rs_code().": ".$Pagadito->get_rs_message());
                        break;
                }
            }

            return redirect($Pagadito->exec_trans($Pagadito->get_rs_code()));
        } else {
            switch($Pagadito->get_rs_code())
            {
                case "PG2001":
                    /*Incomplete data*/
                case "PG3001":
                    /*Problem connection*/
                case "PG3002":
                    /*Error*/
                case "PG3003":
                    /*Unregistered transaction*/
                case "PG3005":
                    /*Disabled connection*/
                case "PG3006":
                    /*Exceeded*/
                default:
                    throw new Exception($Pagadito->get_rs_code().": ".$Pagadito->get_rs_message());
                    break;
            }

        }
    }
     // Get Pagadito credentials
     public function getPagaditoCredentials($output) {
        $gateway = $output['gateway'] ?? null;
        if(!$gateway) throw new Exception(__("Payment gateway not available"));

        $uid_sample = ['UID','uid','u_id'];
        $wsk_sample = ['WSK','wsk','w_sk'];
        $base_url_sample = ['Base URL','base_url','base-url', 'base url'];

        $uid = '';
        $outer_break = false;
        foreach($uid_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->pagaditoPlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->pagaditoPlainText($label);
                if($label == $modify_item) {
                    $uid = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }

        $wsk = '';
        $outer_break = false;
        foreach($wsk_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->pagaditoPlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->pagaditoPlainText($label);

                if($label == $modify_item) {
                    $wsk = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }

        $base_url = '';
        $outer_break = false;
        foreach($base_url_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->pagaditoPlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->pagaditoPlainText($label);

                if($label == $modify_item) {
                    $base_url = $gatewayInput->value ?? "";
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
            'uid'     => $uid,
            'wsk'     => $wsk,
            'base_url'     => $base_url,
            'mode'          => $mode,
        ];

    }

    public function pagaditoPlainText($string) {
        $string = Str::lower($string);
        return preg_replace("/[^A-Za-z0-9]/","",$string);
    }
    public function pagaditoSetSecreteKey($credentials){
        Config::set('pagadito.UID',$credentials->uid);
        Config::set('pagadito.WSK',$credentials->wsk);
        if($credentials->mode == "sandbox"){
            Config::set('pagadito.SANDBOX',true);
        }else{
            Config::set('pagadito.SANDBOX',false);
        }

    }

    public function pagaditoJunkInsert($response,$tokenValue,$env) {
        $output = $this->output;
        $creator_table = $creator_id = $wallet_table = $wallet_id = null;

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
            'env_type'     => $env??"web",
            'gateway'      => $output['gateway']->id,
            'currency'     => $output['currency']->id,
            'amount'       => json_decode(json_encode($output['amount']),true),
            'response'     => $response,
            'wallet_table'  => $wallet_table,
            'wallet_id'     => $wallet_id,
            'creator_table' => $creator_table,
            'creator_id'    => $creator_id,
            'creator_guard' => $creator_guard,
        ];
        Session::put('output',$output);

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::PAGADITO,
            'identifier'    => $tokenValue == ''? generate_unique_string("transactions","trx_id",16): $tokenValue,
            'data'          => $data,
        ]);
    }
    public function pagaditoJunkInsertPayLink($response,$tokenValue) {
        $output = $this->output;
        $wallet_table = $output['wallet']->getTable();
        $wallet_id = $output['wallet']->id;
        $user_relation_name = strtolower($output['user_type'])??'user';

        $data = [
           'type'                   => $output['type'],
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
        Session::put('output',$output);

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::PAGADITO,
            'identifier'    => $tokenValue == ''? generate_unique_string("transactions","trx_id",16): $tokenValue,
            'data'          => $data,
        ]);
    }
    public function pagaditoSuccess($output = null) {
        if(!$output) $output = $this->output;
        $token = $this->output['tempData']['identifier'] ?? "";
        if(empty($token)) throw new Exception(__("Transaction Failed. The record didn't save properly. Please try again"));
        if($output['type'] === PaymentGatewayConst::TYPEADDMONEY){
            if(userGuard()['type'] == "USER"){
                return $this->createTransactionPagadito($output);
            }else{
                return $this->createTransactionChildRecords($output,PaymentGatewayConst::STATUSSUCCESS);
            }
        }else{
            return $this->createTransactionPayLink($output,PaymentGatewayConst::STATUSSUCCESS);
        }

    }
    public function createTransactionPagadito($output) {
        $basic_setting = BasicSettingsProvider::get();
        $user = auth()->user();
        $trx_id = 'AM'.getTrxNum();
        $inserted_id = $this->insertRecordPagadito($output,$trx_id);
        $this->insertChargesPagadito($output,$inserted_id);
        $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSSUCCESS);
        $this->insertDevicePagadito($output,$inserted_id);
        $this->removeTempDataPagadito($output);

        if($this->requestIsApiUser()) {
            // logout user
            $api_user_login_guard = $this->output['api_login_guard'] ?? null;
            if($api_user_login_guard != null) {
                auth()->guard($api_user_login_guard)->logout();
            }
        }
        try{
            if( $basic_setting->email_notification == true){
                $user->notify(new ApprovedMail($user,$output,$trx_id));
            }
        }catch(Exception $e){

        }

    }

    public function insertRecordPagadito($output,$trx_id) {
        $token = $this->output['tempData']['identifier'] ?? "";
        DB::beginTransaction();
        try{
            if(Auth::guard(get_auth_guard())->check()){
                $user_id = auth()->guard(get_auth_guard())->user()->id;
            }
                // Add money
                $trx_id = $trx_id??'AM'.getTrxNum();
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                       => $user_id,
                    'user_wallet_id'                => $output['wallet']->id,
                    'payment_gateway_currency_id'   => $output['currency']->id,
                    'type'                          => $output['type'],
                    'trx_id'                        => $trx_id,
                    'request_amount'                => $output['amount']->requested_amount,
                    'payable'                       => $output['amount']->total_amount,
                    'available_balance'             => $output['wallet']->balance + $output['amount']->requested_amount,
                    'remark'                        => ucwords(remove_speacial_char($output['type']," ")) . " With " . $output['gateway']->name,
                    'details'                       => 'Pagadito Payment Successful',
                    'status'                        => true,
                    'created_at'                    => now(),
                ]);
                $this->updateWalletBalancePagadito($output);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateWalletBalancePagadito($output) {
        $update_amount = $output['wallet']->balance + $output['amount']->requested_amount;
        $output['wallet']->update([
            'balance'   => $update_amount,
        ]);
    }

    public function insertChargesPagadito($output,$id) {
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

    public function insertDevicePagadito($output,$id) {
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

    public function removeTempDataPagadito($output) {
        TemporaryData::where("identifier",$output['tempData']['identifier'])->delete();
    }

     // ********* For API **********
     public function pagaditoInitApi($output = null) {
        $basic_settings = BasicSettingsProvider::get();
        if(!$output) $output = $this->output;
        $credentials = $this->getPagaditoCredentials($output);
        $this->pagaditoSetSecreteKey($credentials);
        $uid = $credentials->uid;
        $wsk = $credentials->wsk;
        $mode = $credentials->mode;
        $Pagadito = new Pagadito($uid,$wsk,$credentials,$output['amount']->sender_cur_code);
        $Pagadito->config( $credentials,$output['amount']->sender_cur_code);
        $env = authGuardApi()['guard'];

        if ($mode == "sandbox") {
            $Pagadito->mode_sandbox_on();
        }
        if ($Pagadito->connect()) {
            $Pagadito->add_detail(1,"Please Pay For ".$basic_settings->site_name." Wallet Add Balance", $output['amount']->total_amount);
            $Pagadito->set_custom_param("param1", "Valor de param1");
            $Pagadito->set_custom_param("param2", "Valor de param2");
            $Pagadito->set_custom_param("param3", "Valor de param3");
            $Pagadito->set_custom_param("param4", "Valor de param4");
            $Pagadito->set_custom_param("param5", "Valor de param5");

            $Pagadito->enable_pending_payments();
            $getUrls = (object)$Pagadito->exec_trans($Pagadito->get_rs_code());

            if($getUrls->code == "PG1002" ){
                $parts = parse_url($getUrls->value);
                parse_str($parts['query'], $query);
                // Extract the token value
                if (isset($query['token'])) {
                    $tokenValue = $query['token'];
                } else {
                    $tokenValue = '';
                }

                $this->pagaditoJunkInsert($getUrls,$tokenValue, $env);
                return $getUrls->value;

            }
            $ern = rand(1000, 2000);
            if (!$Pagadito->exec_trans($ern)) {
                switch($Pagadito->get_rs_code())
                {
                    case "PG2001":
                        /*Incomplete data*/
                    case "PG3002":
                        /*Error*/
                    case "PG3003":
                        /*Unregistered transaction*/
                    case "PG3004":
                        /*Match error*/
                    case "PG3005":
                        /*Disabled connection*/
                    default:
                    $message = ['error' => [$Pagadito->get_rs_code().": ".$Pagadito->get_rs_message()]];
                    Helpers::error($message);
                        break;
                }
            }

            return redirect($Pagadito->exec_trans($Pagadito->get_rs_code()));
        } else {
            switch($Pagadito->get_rs_code())
            {
                case "PG2001":
                    /*Incomplete data*/
                case "PG3001":
                    /*Problem connection*/
                case "PG3002":
                    /*Error*/
                case "PG3003":
                    /*Unregistered transaction*/
                case "PG3005":
                    /*Disabled connection*/
                case "PG3006":
                    /*Exceeded*/
                default:
                    $message = ['error' => [$Pagadito->get_rs_code().": ".$Pagadito->get_rs_message()]];
                    Helpers::error($message);
                    break;
            }

        }
    }
}
