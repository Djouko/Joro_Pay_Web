<?php

namespace Database\Seeders\Admin;

use App\Constants\GlobalConst;
use App\Models\Admin\AppOnboardScreens;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OnboardScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_onboard_screens = array(
            array('type'=>GlobalConst::USER,'title' => 'Easy, Quick & Secure System for Create Virtual Card','sub_title' => 'QRPay has the most secure system which is very useful for money transactions. Get ready to use unlimited virtual credit card system.','image' => 'seeder/onboard1.png','status' => '1','last_edit_by' => '1','created_at' => '2023-05-01 16:33:41','updated_at' => '2023-06-11 12:36:42'),
            array('type'=>GlobalConst::USER,'title' => 'Create Unlimited Virtual Cards for Unlimited Usage','sub_title' => 'Users can easily create virtual credit cards from here. Use anytime anywhere and unlimited. Thanks for start a new journey with QRPAY.','image' => 'seeder/onboard2.png','status' => '1','last_edit_by' => '1','created_at' => '2023-05-01 16:34:33','updated_at' => '2023-06-11 12:36:58'),
            array('type'=>GlobalConst::USER,'title' => 'Create Unlimited Virtual Cards for Unlimited Usage','sub_title' => 'Users can easily create virtual credit cards from here. Use anytime anywhere and unlimited. Thanks for start a new journey with QRPAY.','image' => 'seeder/onboard3.png','status' => '1','last_edit_by' => '1','created_at' => '2023-06-11 12:37:09','updated_at' => '2023-06-11 12:37:18'),

            array('type'=>GlobalConst::AGENT,'title' => 'Get Profit in Every Transaction','sub_title' => 'Start your agency business with QRPay and get the best profit on every transaction','image' => 'seeder/agent/onboard.png','status' => '1','last_edit_by' => '1','created_at' => now(),'updated_at' => now()),

            array('type'=>GlobalConst::MERCHANT,'title' => 'Fast Payment Receiver','sub_title' => 'Easy way to collect payment. fast, secure and reliable platform.','image' => 'seeder/merchant/onboard.png','status' => '1','last_edit_by' => '1','created_at' => now(),'updated_at' => now())
          );
        AppOnboardScreens::insert($app_onboard_screens);
    }
}
