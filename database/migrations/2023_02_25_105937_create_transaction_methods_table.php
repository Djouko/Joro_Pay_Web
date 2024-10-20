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
        Schema::create('transaction_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string("slug")->unique();
            $table->unsignedBigInteger("last_edit_by")->nullable();
            $table->boolean("status")->default(true);
            $table->timestamps();

            $table->foreign("last_edit_by")->references("id")->on("admins")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_methods');
    }
};
