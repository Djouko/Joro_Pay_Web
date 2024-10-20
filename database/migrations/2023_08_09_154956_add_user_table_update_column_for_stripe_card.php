<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTableUpdateColumnForStripeCard extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('stripe_card_holders')->nullable();
            $table->text('stripe_connected_account')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_card_holders');
            $table->dropColumn('stripe_connected_account');
        });
    }
}
