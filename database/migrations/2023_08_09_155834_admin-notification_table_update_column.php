<?php

use App\Constants\PaymentGatewayConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdminNotificationTableUpdateColumn extends Migration
{
    public function up()
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->timestamp("clear_at")->nullable();
        });
    }

    public function down()
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->dropColumn('clear_at');
        });
    }
}
