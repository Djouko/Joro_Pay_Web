<?php

use App\Constants\PaymentGatewayConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentGatewayTableUpdateColumn extends Migration
{
    public function up()
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->enum('env',[
                PaymentGatewayConst::ENV_SANDBOX,
                PaymentGatewayConst::ENV_PRODUCTION,
            ])->comment("Payment Gateway Environment (Ex: Production/Sandbox)")->nullable();
        });
    }

    public function down()
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumn('env');
        });
    }
}
