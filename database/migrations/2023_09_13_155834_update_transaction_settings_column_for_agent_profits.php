<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionSettingsColumnForAgentProfits extends Migration
{

    public function up()
    {
        Schema::table('transaction_settings', function (Blueprint $table) {
            $table->string("slug")->unique()->change();
            $table->decimal('agent_fixed_commissions',8,2,true)->default(0);
            $table->decimal('agent_percent_commissions',8,2,true)->default(0);
            $table->boolean('agent_profit')->default(false);
        });

    }
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('agent_fixed_commissions');
            $table->dropColumn('agent_percent_commissions');
            $table->dropColumn('agent_profit');
        });
    }
}
