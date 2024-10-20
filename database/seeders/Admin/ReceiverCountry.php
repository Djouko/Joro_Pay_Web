<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\ReceiverCounty;
use Illuminate\Database\Seeder;

class ReceiverCountry extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $receiver_counties = array(
            array('id' => '1','admin_id' => '1','country' => 'Australia','name' => 'Australian dollar','code' => 'AUD','mobile_code' => '61','symbol' => '$','flag' => 'e025b912-b8cc-4ff6-be59-a0272349ba10.webp','rate' => '1.50000000','sender' => '0','receiver' => '1','status' => '1','created_at' => '2023-04-04 09:26:35','updated_at' => '2023-04-11 07:09:28'),
            array('id' => '2','admin_id' => '1','country' => 'United Kingdom','name' => 'British pound','code' => 'GBP','mobile_code' => '44','symbol' => '£','flag' => '5cce3c36-c8d5-438e-ac1c-e9b0c9b6ee7d.webp','rate' => '0.80000000','sender' => '0','receiver' => '1','status' => '1','created_at' => '2023-04-04 09:31:35','updated_at' => '2023-04-11 07:02:07'),
            array('id' => '4','admin_id' => '1','country' => 'Canada','name' => 'Canadian dollar','code' => 'CAD','mobile_code' => '1','symbol' => '$','flag' => '685687e9-9dac-40b8-99dd-f950fee6451e.webp','rate' => '1.34000000','sender' => '0','receiver' => '1','status' => '1','created_at' => '2023-04-04 09:40:52','updated_at' => '2023-04-11 07:02:22'),
            array('id' => '5','admin_id' => '1','country' => 'United States','name' => 'United States dollar','code' => 'USD','mobile_code' => '1','symbol' => '$','flag' => '063dd2e0-f98c-4434-b63b-39aff8b01c7b.webp','rate' => '1.00000000','sender' => '0','receiver' => '1','status' => '1','created_at' => '2023-04-04 10:02:56','updated_at' => '2023-04-11 07:02:35'),
            array('id' => '6','admin_id' => '1','country' => 'Bangladesh','name' => 'Bangladeshi taka','code' => 'BDT','mobile_code' => '880','symbol' => '৳','flag' => '2c01811d-b4f9-431c-b866-c2b0b64442fe.webp','rate' => '105.34000000','sender' => '0','receiver' => '1','status' => '1','created_at' => '2023-04-05 07:58:55','updated_at' => '2023-04-11 07:01:37')
          );

          ReceiverCounty::insert($receiver_counties);

    }
}
