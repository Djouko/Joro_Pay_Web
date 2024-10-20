<?php

use App\Constants\GlobalConst;
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
        Schema::create('payment_order_requests', function (Blueprint $table) {
            $table->id();
            $table->text('access_token',500);
            $table->string('token');
            $table->string('trx_id');
            $table->unsignedBigInteger('merchant_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('request_user_type',[
                GlobalConst::USER,
            ]);
            $table->decimal('amount', 28, 8)->default(0);
            $table->string('currency',50)->nullable();
            $table->text('data')->nullable();
            $table->enum('status',[
                PaymentGatewayConst::PENDING,
                PaymentGatewayConst::CREATED,
                PaymentGatewayConst::SUCCESS,
                PaymentGatewayConst::EXPIRED,
            ])->default(PaymentGatewayConst::CREATED);
            $table->boolean('email_verify')->default(false);
            $table->string('email_code',20)->nullable();
            $table->boolean('authentication')->default(false);
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
        Schema::dropIfExists('payment_order_requests');
    }
};
