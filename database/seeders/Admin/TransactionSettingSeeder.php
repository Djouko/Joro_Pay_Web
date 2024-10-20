<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\TransactionSetting;
use Illuminate\Database\Seeder;

class TransactionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transaction_settings = array(
            array('id' => '1','admin_id' => '1','slug' => 'transfer','title' => 'Transfer Money Charges','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '1.00','max_limit' => '1000.00','monthly_limit' => '50000.00','daily_limit' => '5000.00','status' => '1','created_at' => now(),'updated_at' =>now(),'agent_fixed_commissions' => '1.00','agent_percent_commissions' => '1.00','agent_profit' => true),
            array('id' => '2','admin_id' => '1','slug' => 'bill_pay','title' => 'Bill Pay Charges','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '1.00','max_limit' => '1000.00','monthly_limit' => '20000.00','daily_limit' => '2000.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '1.00','agent_percent_commissions' => '1.00','agent_profit' => true),
            array('id' => '3','admin_id' => '1','slug' => 'mobile_topup','title' => 'Mobile Topup Charges','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '15.00','max_limit' => '1000.00','monthly_limit' => '10000.00','daily_limit' => '100000.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '1.00','agent_percent_commissions' => '1.00','agent_profit' => true),
            array('id' => '4','admin_id' => '1','slug' => 'virtual_card','title' => 'Virtual Card Charges','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '100.00','max_limit' => '10000.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '0.00','agent_percent_commissions' => '0.00','agent_profit' => '0'),
            array('id' => '5','admin_id' => '1','slug' => 'remittance','title' => 'Remittance Charge','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '20.00','max_limit' => '15000.00','monthly_limit' => '1000.00','daily_limit' => '1000.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '1.00','agent_percent_commissions' => '1.00','agent_profit' => true),
            array('id' => '6','admin_id' => '1','slug' => 'make-payment','title' => 'Make Payment Charge','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '1.00','max_limit' => '100.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '0.00','agent_percent_commissions' => '0.00','agent_profit' => '0'),
            array('id' => '7','admin_id' => '1','slug' => 'request-money','title' => 'Request Money Charge','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '1.00','max_limit' => '100.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '0.00','agent_percent_commissions' => '0.00','agent_profit' => '0'),
            array('id' => '8','admin_id' => '1','slug' => 'pay-link','title' => 'Pay Link Charges','fixed_charge' => '0.00','percent_charge' => '2.00','min_limit' => '0.00','max_limit' => '0.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '0.00','agent_percent_commissions' => '0.00','agent_profit' => '0'),
            array('id' => '9','admin_id' => '1','slug' => 'money-out','title' => 'Money Out Charges','fixed_charge' => '1.00','percent_charge' => '2.00','min_limit' => '1.00','max_limit' => '1000.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '1.00','agent_percent_commissions' => '1.00','agent_profit' => true),
            array('id' => '10','admin_id' => '1','slug' => 'money-in','title' => 'Money In Charges','fixed_charge' => '1.00','percent_charge' => '2.00','min_limit' => '1.00','max_limit' => '1000.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '1.00','agent_percent_commissions' => '1.00','agent_profit' => true),
            array('id' => '11','admin_id' => '1','slug' => 'reload_card','title' => 'Reload Card Charges','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '2.00','max_limit' => '10000.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '0.00','agent_percent_commissions' => '0.00','agent_profit' => false),
            array('id' => '12','admin_id' => '1','slug' => 'gift_card','title' => 'Gift Card Charges','fixed_charge' => '1.00','percent_charge' => '1.00','min_limit' => '2.00','max_limit' => '10000.00','monthly_limit' => '0.00','daily_limit' => '0.00','status' => '1','created_at' => now(),'updated_at' => now(),'agent_fixed_commissions' => '0.00','agent_percent_commissions' => '0.00','agent_profit' => false),
        );
        TransactionSetting::upsert($transaction_settings,['slug'],['title','agent_fixed_commissions','agent_percent_commissions','agent_profit']);
    }
}
