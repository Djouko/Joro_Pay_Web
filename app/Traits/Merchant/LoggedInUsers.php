<?php

namespace App\Traits\Merchant;

use App\Constants\PaymentGatewayConst;
use App\Models\Admin\Currency;
use App\Models\Merchants\DeveloperApiCredential;
use App\Models\Merchants\GatewaySetting;
use App\Models\Merchants\MerchantLoginLog;
use App\Models\Merchants\MerchantWallet;
use App\Models\Merchants\SandboxWallet;
use Exception;
use Jenssegers\Agent\Agent;

trait LoggedInUsers {

    protected function refreshUserWallets($user) {
        $user_wallets = $user->wallets->pluck("currency_id")->toArray();
        $currencies = Currency::active()->roleHasOne()->pluck("id")->toArray();
        $new_currencies = array_diff($currencies,$user_wallets);
        $new_wallets = [];
        foreach($new_currencies as $item) {
            $new_wallets[] = [
                'merchant_id'       => $user->id,
                'currency_id'   => $item,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            MerchantWallet::insert($new_wallets);
        }catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function createLoginLog($user) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        $data = [
            'merchant_id'       => $user->id,
            'ip'            => $client_ip,
            'mac'           => $mac,
            'city'          => $location['city'] ?? "",
            'country'       => $location['country'] ?? "",
            'longitude'     => $location['lon'] ?? "",
            'latitude'      => $location['lat'] ?? "",
            'timezone'      => $location['timezone'] ?? "",
            'browser'       => $agent->browser() ?? "",
            'os'            => $agent->platform() ?? "",
        ];

        try{
            MerchantLoginLog::create($data);
        }catch(Exception $e) {
            // return false;
        }
    }
    protected function refreshSandboxWallets($user) {

        $user_wallets = $user->sandboxWallets->pluck("currency_id")->toArray();
        $currencies = Currency::active()->roleHasOne()->pluck("id")->toArray();
        $new_currencies = array_diff($currencies,$user_wallets);
        $new_wallets = [];
        foreach($new_currencies as $item) {
            $new_wallets[] = [
                'merchant_id'   => $user->id,
                'currency_id'   => $item,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            SandboxWallet::insert($new_wallets);
        }catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    protected function createDeveloperApi($user) {
        $developing_api = DeveloperApiCredential::where('merchant_id',$user->id)->first();
        try{
            if($developing_api){
                $developing_api->merchant_id  = $developing_api->merchant_id;
                $developing_api->client_id  = $developing_api->client_id;
                $developing_api->client_secret  = $developing_api->client_secret;
                $developing_api->mode  = $developing_api->mode;
                $developing_api->save();

            }else{
            DeveloperApiCredential::create([
                'merchant_id'       => $user->id,
                'client_id'         => generate_unique_string("developer_api_credentials", "client_id", 100),
                'client_secret'     => generate_unique_string("developer_api_credentials", "client_secret", 100),
                'mode'              => PaymentGatewayConst::ENV_SANDBOX,
                'status'            => true,
                'created_at'        => now(),
            ]);
        }

        }catch(Exception $e) {

            return throw new Exception(__("Failed to create developer API. Something went wrong!"));
        }
    }
    protected function createGatewaySetting($user) {
        $gateway_setting = GatewaySetting::where('merchant_id',$user->id)->first();
        try{
            if($gateway_setting){
                $gateway_setting->merchant_id  = $gateway_setting->merchant_id;
                $gateway_setting->wallet_status  = $gateway_setting->wallet_status;
                $gateway_setting->virtual_card_status  = $gateway_setting->virtual_card_status;
                $gateway_setting->master_visa_status  = $gateway_setting->master_visa_status;
                $gateway_setting->save();
            }else{
                GatewaySetting::create([
                'merchant_id'               => $user->id,
                'wallet_status'             => true,
                'virtual_card_status'       => true,
                'master_visa_status'        => false,
                'credentials'               => [
                                                'primary_key' => '',
                                                'secret_key' => ''
                                                ],
                'created_at'        => now(),
            ]);
        }

        }catch(Exception $e) {

            return throw new Exception("Failed to create Gateway Settings. Something went wrong!");
        }
    }
}
