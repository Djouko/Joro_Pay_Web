<?php

namespace Database\Seeders\Update;

use App\Models\Admin\AppSettings;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_settings = array(
            'version' => '4.5.0',
            'agent_version' => '4.5.0',
            'merchant_version' => '4.5.0',
          );
        $appSettings = AppSettings::first();
        $appSettings->update($app_settings);
    }
}
