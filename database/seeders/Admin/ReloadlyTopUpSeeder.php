<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\ReloadlyApi;
use Illuminate\Database\Seeder;

class ReloadlyTopUpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mobile_topup_apis = array(
            array('provider' => 'RELOADLY','type' => 'MOBILE-TOPUP','credentials' => '{"client_id":"zdEpKtHis9zKyuQI89ctF7tfswm5HEyN","secret_key":"weHaLOnFmO-OxuQ8nHGh91ilR8GIuE-Q4BTyFJOK5sH33mwPDw39BtYTwPgAtQv","production_base_url":"https://topups.reloadly.com","sandbox_base_url":"https://topups-sandbox.reloadly.com"}','status' => '1','env' => 'sandbox','created_at' =>now(),'updated_at' =>now())
          );

        ReloadlyApi::insert($mobile_topup_apis);
    }
}
