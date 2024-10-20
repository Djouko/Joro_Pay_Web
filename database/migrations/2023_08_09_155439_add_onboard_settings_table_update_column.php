<?php

use App\Constants\GlobalConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnboardSettingsTableUpdateColumn extends Migration
{
    public function up()
    {
        Schema::table('app_onboard_screens', function (Blueprint $table) {
            $table->enum("type",[
                GlobalConst::USER,
                GlobalConst::AGENT,
                GlobalConst::MERCHANT
            ]);
        });
    }
    public function down()
    {
        Schema::table('app_onboard_screens', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
