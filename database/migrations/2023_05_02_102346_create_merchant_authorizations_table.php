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
        Schema::create('merchant_authorizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("merchant_id");
            $table->string("email")->nullable();
            $table->integer("code");
            $table->text("token")->nullable();
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
        Schema::dropIfExists('merchant_authorizations');
    }
};
