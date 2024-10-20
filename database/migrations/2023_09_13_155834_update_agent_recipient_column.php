<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAgentRecipientColumn extends Migration
{

    public function up()
    {
        Schema::table('agent_recipients', function (Blueprint $table) {
            $table->string('account_number',20)->nullable()->after('mobile');
        });
    }
    public function down()
    {
        Schema::table('agent_recipients', function (Blueprint $table) {
            $table->dropColumn('account_number');
        });
    }
}
