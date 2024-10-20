<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway_a_pis', function (Blueprint $table) {
            $table->boolean('wallet_status')->default(1)->after('admin_id');
            $table->boolean('card_status')->default(1)->after('admin_id');
            $table->boolean('payment_gateway_status')->default(1)->after('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
