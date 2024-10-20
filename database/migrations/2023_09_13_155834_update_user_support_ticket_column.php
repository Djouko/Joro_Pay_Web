<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserSupportTicketColumn extends Migration
{

    public function up()
    {
        Schema::table('user_support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('merchant_id');

            $table->foreign("agent_id")->references("id")->on("agents")->onDelete("cascade")->onUpdate("cascade");
        });
    }
    public function down()
    {
        Schema::table('user_support_tickets', function (Blueprint $table) {
            $table->dropColumn('agent_id');
        });
    }
}
