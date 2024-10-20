<?php

namespace Database\Seeders\Update;


use Illuminate\Database\Seeder;

class UpdateEnvData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $env_modify_keys = [
            "QUEUE_CONNECTION"      => "database"
        ];

        modifyEnv($env_modify_keys);
    }
}
