<?php

use App\Constants\GlobalConst;
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
        Schema::create('agent_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->integer('country');
            $table->string('type',100)->nullable();
            $table->enum("recipient_type",[
                GlobalConst::SENDER,
                GlobalConst::RECEIVER
            ]);
            $table->string('alias',100)->nullable();
            $table->string('firstname',100)->nullable();
            $table->string('lastname',100)->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_code',10)->nullable();
            $table->string('mobile');
            $table->string('city',100)->nullable();
            $table->string('address',255)->nullable();
            $table->string('state',255)->nullable();
            $table->string('zip_code',10)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_recipients');
    }
};
