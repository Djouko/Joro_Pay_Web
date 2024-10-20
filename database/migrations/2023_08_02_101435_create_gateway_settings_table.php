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
        Schema::create('gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("merchant_id")->nullable();
            $table->boolean('wallet_status')->default(1);
            $table->boolean('virtual_card_status')->default(1);
            $table->boolean('master_visa_status')->default(1);
            $table->text('credentials')->nullable();
            $table->timestamps();

            $table->foreign("merchant_id")->references("id")->on("merchants")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gateway_settings');
    }
};
