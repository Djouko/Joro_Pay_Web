<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\AdminHasRole;
use App\Models\Admin\CountryRestriction;
use Illuminate\Database\Seeder;

class CountryRestrictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $country_restrictions = array(
            array('admin_id' => '1','slug' => 'user','user_type' => 'USER','data' => '[]','status' => '1','created_at' => now(),'updated_at' =>now()),
            array('admin_id' => '1','slug' => 'agent','user_type' => 'AGENT','data' => '[]','status' => '1','created_at' => now(),'updated_at' =>now()),
            array('admin_id' => '1','slug' => 'merchant','user_type' => 'MERCHANT','data' => '[]','status' => '1','created_at' => now(),'updated_at' =>now())
        );

        CountryRestriction::upsert($country_restrictions,['slug'],['user_type','data']);
    }
}
