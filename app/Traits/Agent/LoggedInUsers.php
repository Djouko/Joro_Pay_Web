<?php

namespace App\Traits\Agent;

use App\Models\Admin\Currency;
use App\Models\AgentLoginLog;
use App\Models\AgentWallet;
use Exception;
use Jenssegers\Agent\Agent;

trait LoggedInUsers {

    protected function refreshUserWallets($user) {
        $user_wallets = $user->wallet->pluck("currency_id")->toArray();
        $currencies = Currency::active()->roleHasOne()->pluck("id")->toArray();
        $new_currencies = array_diff($currencies,$user_wallets);
        $new_wallets = [];
        foreach($new_currencies as $item) {
            $new_wallets[] = [
                'agent_id'       => $user->id,
                'currency_id'   => $item,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            AgentWallet::insert($new_wallets);
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
            'agent_id'       => $user->id,
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
            AgentLoginLog::create($data);
        }catch(Exception $e) {
            // return false;
        }
    }
}
