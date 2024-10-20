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
        Schema::create('gateway_a_pis', function (Blueprint $table) {
            $table->id();
            $table->foreignId("admin_id")->constrained('admins')->cascadeOnDelete();
            $table->text('secret_key')->nullable();
            $table->text('public_key')->nullable();
            $table->text('account_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gateway_a_pis');
    }
};
