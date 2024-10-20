<?php

namespace App\Traits\Merchant;

use App\Constants\PaymentGatewayConst;
use App\Models\Admin\Currency;
use App\Models\Merchants\DeveloperApiCredential;
use App\Models\Merchants\MerchantWallet;
use App\Models\Merchants\SandboxWallet;
use Exception;

trait RegisteredUsers {
    protected function createUserWallets($user) {
        $currencies = Currency::active()->roleHasOne()->pluck("id")->toArray();
        $wallets = [];
        foreach($currencies as $currency_id) {
            $wallets[] = [
                'merchant_id'   => $user->id,
                'currency_id'   => $currency_id,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            MerchantWallet::insert($wallets);
        }catch(Exception $e) {
            // handle error
            $this->guard()->logout();
            $user->delete();
            return $this->breakAuthentication(__("Failed to create wallet! Please try again"));
        }
    }


    protected function breakAuthentication($error) {
        return back()->with(['error' => [$error]]);
    }

    protected function createDeveloperApiReg($user) {
        try{
            DeveloperApiCredential::create([
                'merchant_id'       => $user->id,
                'name'              => 'Test Name',
                'client_id'         => generate_unique_string("developer_api_credentials","client_id",100),
                'client_secret'     => generate_unique_string("developer_api_credentials","client_secret",100),
                'mode'              => PaymentGatewayConst::ENV_SANDBOX,
                'status'            => true,
                'created_at'        => now(),
            ]);

            // create developer sandbox wallets
            $this->createSandboxWallets($user);
        }catch(Exception $e) {

            return throw new Exception(__("Failed to create developer API. Something went wrong!"));
        }
    }

    protected function createSandboxWallets($user) {
        if(!$user->developerApi) return false;

        $currencies =Currency::active()->roleHasOne()->pluck("id")->toArray();
        $wallets = [];
        foreach($currencies as $currency_id) {
            $wallets[] = [
                'merchant_id'   => $user->id,
                'currency_id'   => $currency_id,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            SandboxWallet::insert($wallets);
        }catch(Exception $e) {
            return throw new Exception(__("Something went wrong! Please try again."));
        }
    }
}
