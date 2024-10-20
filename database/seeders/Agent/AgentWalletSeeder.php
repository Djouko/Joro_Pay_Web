<?php

namespace Database\Seeders\Agent;

use App\Models\Admin\Currency;
use App\Models\AgentWallet;
use Illuminate\Database\Seeder;

class AgentWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies_ids = Currency::roleHasOne()->active()->get()->pluck("id")->toArray();
        $user_ids = [1,2];
        foreach($user_ids as $user_id) {
            foreach($currencies_ids as $currency_id) {
                $data[] = [
                    'agent_id'       => $user_id,
                    'currency_id'   => $currency_id,
                    'balance'       => 1000,
                    'status'        => true,
                ];
            }
        }

        AgentWallet::upsert($data,['agent_id','currency_id'],['balance']);
    }
}
