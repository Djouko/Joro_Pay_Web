<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransactionColumnForAgent extends Migration
{

    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('merchant_wallet_id');
            $table->unsignedBigInteger('agent_wallet_id')->nullable()->after('agent_id');

            $table->foreign("agent_id")->references("id")->on("agents")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("agent_wallet_id")->references("id")->on("agent_wallets")->onDelete("cascade")->onUpdate("cascade");
        });
    }
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('agent_id');
            $table->dropColumn('agent_wallet_id');
        });
    }
}
