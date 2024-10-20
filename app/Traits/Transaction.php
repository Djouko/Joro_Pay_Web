<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

trait Transaction {
    public function createTransactionChildRecords(int $transaction_id,object $charges) {
        $this->createTransactionChargeRecord($transaction_id,$charges);
        $this->createTransactionDeviceRecord($transaction_id);
    }

    public function createTransactionChargeRecord(int $transaction_id,object $charges) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $transaction_id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      => $charges->fixed_charge,
                'total_charge'      => $charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function createTransactionDeviceRecord(int $transaction_id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $transaction_id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}