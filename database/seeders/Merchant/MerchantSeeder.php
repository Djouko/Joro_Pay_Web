<?php

namespace Database\Seeders\Merchant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Constants\PaymentGatewayConst;
use App\Models\Merchants\Merchant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'firstname'         => "Test",
                'lastname'          => "Merchant",
                'business_name'     => "Test Business Name",
                'email'             => "merchant@appdevs.net",
                'username'          => "testmerchant",
                'mobile_code'       => "880",
                'mobile'            => "123456789",
                'full_mobile'       => "880123456789",
                'status'            => true,
                'password'          => Hash::make("appdevs"),
                'address'           => '{"country":"Bangladesh","city":"Dhaka","zip":"1230","state":"","address":""}',
                'email_verified'    => true,
                'sms_verified'      => true,
                'kyc_verified'      => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        ];

        Merchant::insert($data);

    }
}
