<?php

namespace Database\Seeders\Admin;

use App\Models\TopupCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TopupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         //make bill pay seeder
         $data = [
            "Airtel"                => 'airtel',
            "AT&T Mobility"         => 'att-mobility',
            "Mobile Place"          => 'mobile-place',
            "Mobile Garage"         => 'mobile-garage',
        ];
        $create = [];
        foreach($data as $name => $slug) {
            $create[] = [
                'admin_id'          => 1,
                'slug'              => $slug,
                'name'             => $name,
                'status'         => 1,
                'created_at'     => now(),

            ];
        }
        TopupCategory::insert($create);
    }
}
