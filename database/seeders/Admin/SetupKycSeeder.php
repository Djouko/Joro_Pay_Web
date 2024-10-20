<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin\SetupKyc;
use App\Constants\GlobalConst;

class SetupKycSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setup_kycs = array(
            array('id' => '1','slug' => 'user','user_type' => 'USER','fields' => '[{"type":"file","label":"Id Back Part","name":"id_back_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}},{"type":"file","label":"Id Front Part","name":"id_front_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}}]','status' => '1','last_edit_by' => '1','created_at' => '2023-02-20 05:21:32','updated_at' => '2023-03-20 04:05:57'),
            array('id' => '2','slug' => 'merchant','user_type' => 'MERCHANT','fields' => '[{"type":"file","label":"Id Back Part","name":"id_back_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}},{"type":"file","label":"Id Front Part","name":"id_front_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}}]','status' => '1','last_edit_by' => '1','created_at' => '2023-05-02 17:18:26','updated_at' => '2023-05-02 17:25:46'),
            array('id' => '3','slug' => 'agent','user_type' => 'AGENT','fields' => '[{"type":"file","label":"Id Back Part","name":"id_back_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}},{"type":"file","label":"Id Front Part","name":"id_front_part","required":true,"validation":{"max":"10","mimes":["jpg","png","svg","webp"],"min":0,"options":[],"required":true}}]','status' => '1','last_edit_by' => '1','created_at' => '2024-03-01 15:30:15','updated_at' => '2024-03-01 15:30:15')
          );

         SetupKyc::insert($setup_kycs);
    }
}
