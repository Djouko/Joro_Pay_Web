<?php

use App\Constants\PaymentGatewayConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTransactionColumn extends Migration
{

    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string("type")->change();
            $table->string('callback_ref')->nullable();
            $table->unsignedBigInteger("payment_link_id")->nullable()->after('payment_gateway_currency_id');
            $table->foreign("payment_link_id")->references("id")->on("payment_links")->onDelete("cascade")->onUpdate("cascade");
        });
    }
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('callback_ref');
            $table->dropColumn('payment_link_id');
        });
    }
}
