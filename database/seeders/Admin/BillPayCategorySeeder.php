<?php

namespace Database\Seeders\Admin;

use App\Models\BillPayCategory;
use Illuminate\Database\Seeder;

class BillPayCategorySeeder extends Seeder
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
            "Insurance"         => 'insurance',
            "Govt.Fees"         => 'govtfees',
            "Education"         => 'education',
            "Internet"          => 'internet',
            "Telephone"         => 'telephone',
            "Gas"               => 'gas',
            "Electricity"       => 'electricity',

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
        BillPayCategory::insert($create);
    }
}
