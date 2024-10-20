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
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('currency');
            $table->string('currency_symbol');
            $table->string('currency_name');
            $table->string('country');
            $table->string('type');
            $table->string('token')->unique();
            $table->string('title');
            $table->string('image')->nullable();
            $table->text('details')->nullable();
            $table->enum('limit', [1,2])->comment('1:limited, 2:unlimited')->nullable();
            $table->decimal('min_amount', 28,16,true)->nullable();
            $table->decimal('max_amount', 28,16,true)->nullable();
            $table->decimal('price', 28,16,true)->nullable();
            $table->integer('qty')->nullable();
            $table->text('reject_reason')->nullable();
            $table->enum('status', [1,2,3])->default(2)->comment('1=Active, 2=Closed, 3=Rejected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_links');
    }
};
