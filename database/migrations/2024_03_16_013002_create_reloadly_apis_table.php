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
        Schema::create('reloadly_apis', function (Blueprint $table) {
            $table->id();
            $table->string('provider',250)->comment('Provider slug');
            $table->string('type',250)->unique()->comment('Type slug');
            $table->text('credentials')->comment('configuration credentials');
            $table->tinyInteger('status')->comment('1: Active, 2: Deactivate');
            $table->string('env',100)->default(GlobalConst::ENV_SANDBOX)->comment("environment");
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
        Schema::dropIfExists('reloadly_apis');
    }
};
