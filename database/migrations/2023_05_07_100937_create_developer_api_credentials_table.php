<?php

use App\Constants\PaymentGatewayConst;
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
        Schema::create('developer_api_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_id');
            $table->string('client_id');
            $table->string('client_secret');
            $table->enum('mode',[
                PaymentGatewayConst::ENV_PRODUCTION,
                PaymentGatewayConst::ENV_SANDBOX,
            ]);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('developer_api_credentials');
    }
};
