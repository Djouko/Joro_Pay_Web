<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\MerchantConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerchantConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'name'          => "QRPay",
            'image'         => "seeder/payment-gateway-logo-default-1.png",
            'version'       => '1.0.0',
            'sms_verify'    => false,
            'email_verify'  => false,
        ];

        MerchantConfiguration::updateOrCreate(['id' => 1],$data);
    }
}
