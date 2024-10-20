<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\BasicSettings;
use Illuminate\Database\Seeder;

class BasicSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'site_name'                     => "QRPAY",
            'site_title'                    => "Money Transfer with QR Code",
            'agent_site_name'               => "QRPay Agent",
            'agent_site_title'              => "Retailer Business with QR Code",
            'merchant_site_name'            => "QRPay Merchant",
            'merchant_site_title'           => "Accept Payment via QR Code",
            'base_color'                    => "#0C56DB",
            'agent_base_color'              => "#007A5A",
            'merchant_base_color'           => "#009CDE",
            'web_version'                   => "4.5.0",
            'secondary_color'               => "#000400",
            'otp_exp_seconds'               => "3600",
            'agent_otp_exp_seconds'         => "3600",
            'merchant_otp_exp_seconds'      => "3600",
            'timezone'                      => "Asia/Dhaka",
            'broadcast_config'  => [
                "method" => "pusher",
                "app_id" => "1574360",
                "primary_key" => "971ccaa6176db78407bf",
                "secret_key" => "a30a6f1a61b97eb8225a",
                "cluster" => "ap2"
            ],
            'push_notification_config'  => [
                "method" => "pusher",
                "instance_id" => "255ae045-4995-4b74-9caf-b9b5101780df",
                "primary_key" => "CDBB1D7FC33B562C63019647D3076998A14B97B251F651CB72B3934E49114200"
            ],

            'mail_config'       => [
                "method" => "smtp",
                "host" => "appdevs.team",
                "port" => "465",
                "encryption" => "ssl",
                "username" => "system@appdevs.net",
                "password" => "QP2fsLk?80Ac",
                "from" => "system@appdevs.net",
                "app_name" => "QRPAY",
            ],
            'kyc_verification'                  => true,
            'email_verification'                => true,
            'user_registration'                 => true,
            'agree_policy'                      => true,
            'email_notification'                => true,
            'push_notification'                 => true,
            'agent_kyc_verification'            => true,
            'agent_email_verification'          => true,
            'agent_registration'                => true,
            'agent_agree_policy'                => true,
            'agent_email_notification'          => true,
            'agent_push_notification'           => true,
            'merchant_kyc_verification'         => true,
            'merchant_email_verification'       => true,
            'merchant_registration'             => true,
            'merchant_agree_policy'             => true,
            'merchant_email_notification'       => true,
            'merchant_push_notification'        => true,
            'site_logo_dark'                    => "seeder/logo-white.png",
            'site_logo'                         => "seeder/logo-dark.png",
            'site_fav_dark'                     => "seeder/favicon-dark.png",
            'site_fav'                          => "seeder/favicon-white.png",
            'agent_site_logo_dark'              => "seeder/agent/logo-white.png",
            'agent_site_logo'                   => "seeder/agent/logo-dark.png",
            'agent_site_fav_dark'               => "seeder/agent/favicon-dark.png",
            'agent_site_fav'                    => "seeder/agent/favicon-white.png",
            'merchant_site_logo_dark'           => "seeder/merchant/logo-white.png",
            'merchant_site_logo'                => "seeder/merchant/logo-dark.png",
            'merchant_site_fav_dark'            => "seeder/merchant/favicon-dark.png",
            'merchant_site_fav'                 => "seeder/merchant/favicon-white.png",
        ];
        BasicSettings::truncate();
        BasicSettings::firstOrCreate($data);
    }
}
