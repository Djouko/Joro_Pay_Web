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
        Schema::create('receiver_counties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('country',100)->index();
            $table->string('name',100)->index();
            $table->string('code',20)->index();
            $table->string('mobile_code',20)->nullable();
            $table->string('symbol',20);
            $table->string('flag',191)->unique()->nullable();
            $table->decimal('rate',28,8)->default(1);
            $table->boolean('sender')->default(false);
            $table->boolean('receiver')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receiver_counties');
    }
};
