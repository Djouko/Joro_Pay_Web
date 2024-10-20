<?php

namespace Database\Seeders\Agent;

use App\Models\Agent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data = [
            [
                'firstname'         => "Test",
                'lastname'          => "Agent",
                'email'             => "agent@appdevs.net",
                'store_name'        => "Appdevs",
                'username'          => "testagent",
                'mobile_code'       => "880",
                'mobile'            => "123456789",
                'full_mobile'       => "880123456789",
                'status'            => true,
                'password'          => Hash::make("appdevs"),
                'address'           => '{"country":"Bangladesh","city":"Dhaka","zip":"1230","state":"Bangladesh","address":"Dhaka,Bangladesh"}',
                'email_verified'    => true,
                'sms_verified'      => true,
                'kyc_verified'      => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'firstname'         => "Test",
                'lastname'          => "Agent 2",
                'email'             => "agent2@appdevs.net",
                'store_name'        => "AppdevsX",
                'username'          => "testagent2",
                'mobile_code'       => "880",
                'mobile'            => "123456781",
                'full_mobile'       => "880123456781",
                'status'            => true,
                'password'          => Hash::make("appdevs"),
                'address'           => '{"country":"Bangladesh","city":"Dhaka","zip":"1230","state":"Bangladesh","address":"Dhaka,Bangladesh"}',
                'email_verified'    => true,
                'sms_verified'      => true,
                'kyc_verified'      => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],

        ];

        Agent::insert($data);
    }
}
