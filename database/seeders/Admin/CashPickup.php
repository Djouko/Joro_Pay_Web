<?php

namespace Database\Seeders\Admin;

use App\Models\RemitanceCashPickup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashPickup extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $remitance_cash_pickups = array(
            array('id' => '2','admin_id' => '1','name' => 'Bank Alfalah','alias' => 'bank-alfalah','status' => '1','created_at' => '2023-04-04 12:15:07','updated_at' => '2023-04-05 04:48:20')
          );

          RemitanceCashPickup::insert($remitance_cash_pickups);

    }
}
