<?php

namespace Database\Seeders\Admin;

use App\Models\RemitanceBankDeposit;
use Illuminate\Database\Seeder;
class BankTransfer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $remitance_bank_deposits = array(
            array('id' => '2','admin_id' => '1','name' => 'United Bank Limited','alias' => 'united-bank-limited','status' => '1','created_at' => '2023-04-04 11:24:08','updated_at' => '2023-04-04 11:32:34'),
            array('id' => '3','admin_id' => '1','name' => 'UBL Bank Limited','alias' => 'ubl-bank-limited','status' => '1','created_at' => '2023-04-04 11:32:50','updated_at' => '2023-04-04 11:32:50'),
            array('id' => '4','admin_id' => '1','name' => 'NIB Bank Limited','alias' => 'nib-bank-limited','status' => '1','created_at' => '2023-04-04 11:33:04','updated_at' => '2023-04-04 11:33:04'),
            array('id' => '5','admin_id' => '1','name' => 'MCB Bank Limited','alias' => 'mcb-bank-limited','status' => '1','created_at' => '2023-04-04 11:33:17','updated_at' => '2023-04-04 11:38:36')
          );

          RemitanceBankDeposit::insert($remitance_bank_deposits);
    }
}
