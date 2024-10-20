<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppSettingsTableUpdateColumn extends Migration
{
    public function up()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('agent_version',50)->nullable();
            $table->string('merchant_version',50)->nullable();
            $table->string('agent_splash_screen_image',255)->nullable();
            $table->string('merchant_splash_screen_image',255)->nullable();
        });
    }
    public function down()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn('agent_version');
            $table->dropColumn('merchant_version');
            $table->dropColumn('agent_splash_screen_image');
            $table->dropColumn('merchant_splash_screen_image');
        });
    }
}
