<?php

namespace Database\Seeders\Update;

use App\Constants\ModuleSetting;
use App\Models\Admin\ModuleSetting as AdminModuleSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         //make module for user
        if(!AdminModuleSetting::where('slug','gift-cards')->exists()){
            $data = [
                ModuleSetting::GIFTCARDS                => 'Gift Cards',

            ];
            $create = [];
            foreach($data as $slug => $item) {
                $create[] = [
                    'admin_id'          => 1,
                    'slug'              => $slug,
                    'user_type'         => "USER",
                    'status'            => true,
                    'created_at'        => now(),
                ];
            }
            AdminModuleSetting::insert($create);

        }



    }
}
