<?php

namespace Database\Seeders\Merchant;

use App\Models\Admin\Currency;
use App\Models\Merchants\MerchantWallet;
use Illuminate\Database\Seeder;

class MerchantWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies_ids = Currency::roleHasOne()->active()->get()->pluck("id")->toArray();

        $merchant_ids = [1];

        foreach($merchant_ids as $merchant_id) {
            foreach($currencies_ids as $currency_id) {
                $data[] = [
                    'merchant_id'   => $merchant_id,
                    'currency_id'   => $currency_id,
                    'balance'       => 1000,
                    'status'        => true,
                ];
            }
        }

        MerchantWallet::upsert($data,['merchant_id','currency_id'],['balance']);
    }
}
