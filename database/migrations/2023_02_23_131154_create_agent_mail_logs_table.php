<?php

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
        Schema::create('agent_mail_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("agent_id");
            $table->string("method")->nullable();
            $table->string("subject",255);
            $table->text("message",3000);
            $table->timestamps();

            $table->foreign("agent_id")->references("id")->on("agents")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_mail_logs');
    }
};
