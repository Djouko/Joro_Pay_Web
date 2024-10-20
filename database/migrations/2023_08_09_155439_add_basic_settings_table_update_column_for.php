<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBasicSettingsTableUpdateColumnFor extends Migration
{
    public function up()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->integer('otp_resend_seconds')->nullable();

            $table->string('agent_site_name',100)->nullable();
            $table->string('agent_site_title',255)->nullable();
            $table->string('agent_base_color',50)->nullable();
            $table->integer('agent_otp_resend_seconds')->nullable();
            $table->integer('agent_otp_exp_seconds')->nullable();
            $table->string('agent_site_logo_dark',255)->nullable();
            $table->string('agent_site_logo',255)->nullable();
            $table->string('agent_site_fav_dark',255)->nullable();
            $table->string('agent_site_fav',255)->nullable();
            $table->boolean('agent_registration')->default(true);
            $table->boolean('agent_secure_password')->default(false);
            $table->boolean('agent_agree_policy')->default(false);
            $table->boolean('agent_email_verification')->default(false);
            $table->boolean('agent_email_notification')->default(false);
            $table->boolean('agent_push_notification')->default(false);
            $table->boolean('agent_kyc_verification')->default(false);

            $table->string('merchant_site_name',100)->nullable();
            $table->string('merchant_site_title',255)->nullable();
            $table->integer('merchant_otp_exp_seconds')->nullable();
            $table->integer('merchant_otp_resend_seconds')->nullable();
            $table->string('merchant_base_color',50)->nullable();
            $table->string('merchant_site_logo_dark',255)->nullable();
            $table->string('merchant_site_logo',255)->nullable();
            $table->string('merchant_site_fav_dark',255)->nullable();
            $table->string('merchant_site_fav',255)->nullable();
            $table->boolean('merchant_registration')->default(true);
            $table->boolean('merchant_secure_password')->default(false);
            $table->boolean('merchant_agree_policy')->default(false);
            $table->boolean('merchant_email_verification')->default(false);
            $table->boolean('merchant_email_notification')->default(false);
            $table->boolean('merchant_push_notification')->default(false);
            $table->boolean('merchant_kyc_verification')->default(false);



        });
    }
    public function down()
    {
        Schema::table('basic_settings', function (Blueprint $table) {
            $table->dropColumn('otp_resend_seconds');

            $table->dropColumn('agent_site_name');
            $table->dropColumn('agent_site_title');
            $table->dropColumn('agent_base_color');
            $table->dropColumn('agent_otp_exp_seconds');
            $table->dropColumn('agent_otp_resend_seconds');
            $table->dropColumn('agent_site_logo_dark');
            $table->dropColumn('agent_site_logo');
            $table->dropColumn('agent_site_fav_dark');
            $table->dropColumn('agent_site_fav');
            $table->dropColumn('agent_registration');
            $table->dropColumn('agent_secure_password');
            $table->dropColumn('agent_agree_policy');
            $table->dropColumn('agent_email_verification');
            $table->dropColumn('agent_email_notification');
            $table->dropColumn('agent_push_notification');
            $table->dropColumn('agent_kyc_verification');

            $table->dropColumn('merchant_site_name');
            $table->dropColumn('merchant_site_title');
            $table->dropColumn('merchant_base_color');
            $table->dropColumn('merchant_otp_exp_seconds');
            $table->dropColumn('merchant_otp_resend_seconds');
            $table->dropColumn('merchant_site_logo_dark');
            $table->dropColumn('merchant_site_logo');
            $table->dropColumn('merchant_site_fav_dark');
            $table->dropColumn('merchant_site_fav');
            $table->dropColumn('merchant_registration');
            $table->dropColumn('merchant_secure_password');
            $table->dropColumn('merchant_agree_policy');
            $table->dropColumn('merchant_email_verification');
            $table->dropColumn('merchant_email_notification');
            $table->dropColumn('merchant_push_notification');
            $table->dropColumn('merchant_kyc_verification');
        });
    }
}
