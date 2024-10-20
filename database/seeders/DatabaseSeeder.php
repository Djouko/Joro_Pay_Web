<?php

namespace Database\Seeders;


use Database\Seeders\Admin\AdminHasRoleSeeder;
use Database\Seeders\Admin\ExchangeRateSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\Admin\AdminSeeder;
use Database\Seeders\Admin\CurrencySeeder;
use Database\Seeders\Admin\SetupKycSeeder;
use Database\Seeders\Admin\SetupSeoSeeder;
use Database\Seeders\Admin\ExtensionSeeder;
use Database\Seeders\Admin\AppSettingsSeeder;
use Database\Seeders\Admin\BankTransfer;
use Database\Seeders\Admin\SiteSectionsSeeder;
use Database\Seeders\Admin\BasicSettingsSeeder;
use Database\Seeders\Admin\BillPayCategorySeeder;
use Database\Seeders\Admin\BlogSeeder;
use Database\Seeders\Admin\CashPickup;
use Database\Seeders\Admin\CountryRestrictionSeeder;
use Database\Seeders\Admin\GatewayApiSeeder;
use Database\Seeders\Admin\GiftCardApiSeeder;
use Database\Seeders\Admin\LanguageSeeder;
use Database\Seeders\Admin\MerchantConfigurationSeeder;
use Database\Seeders\Admin\ModuleSettingSeeder;
use Database\Seeders\Admin\OnboardScreenSeeder;
use Database\Seeders\Admin\PaymentGatewaySeeder;
use Database\Seeders\Admin\ReceiverCountry;
use Database\Seeders\Admin\ReloadlyTopUpSeeder;
use Database\Seeders\Admin\RoleSeeder;
use Database\Seeders\Admin\SetupEmailSeeder;
use Database\Seeders\Admin\SetupPageSeeder;
use Database\Seeders\Admin\TopupSeeder;
use Database\Seeders\Admin\TransactionSettingSeeder;
use Database\Seeders\Admin\UpdateEnvData;
use Database\Seeders\Admin\UtilityPaymentApiSeeder;
use Database\Seeders\Admin\VirtualApiSeeder;
use Database\Seeders\Agent\AgentSeeder;
use Database\Seeders\Agent\AgentWalletSeeder;
use Database\Seeders\Fresh\BasicSettingsSeeder as FreshBasicSettingsSeeder;
use Database\Seeders\Fresh\ExtensionSeeder as FreshExtensionSeeder;
use Database\Seeders\Merchant\ApiCredentialsSeeder;
use Database\Seeders\Merchant\MerchantSandboxWalletSeeder;
use Database\Seeders\Merchant\MerchantSeeder;
use Database\Seeders\Merchant\MerchantWalletSeeder;
use Database\Seeders\User\UserSeeder;
use Database\Seeders\User\UserWalletSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //demo
        // $this->call([
        //     AdminSeeder::class,
        //     RoleSeeder::class,
        //     TransactionSettingSeeder::class,
        //     CurrencySeeder::class,
        //     BasicSettingsSeeder::class,
        //     BillPayCategorySeeder::class,
        //     TopupSeeder::class,
        //     LanguageSeeder::class,
        //     PaymentGatewaySeeder::class,
        //     SetupSeoSeeder::class,
        //     AppSettingsSeeder::class,
        //     OnboardScreenSeeder::class,
        //     SiteSectionsSeeder::class,
        //     SetupKycSeeder::class,
        //     ExtensionSeeder::class,
        //     BlogSeeder::class,
        //     BankTransfer::class,
        //     CashPickup::class,
        //     ReceiverCountry::class,
        //     AdminHasRoleSeeder::class,
        //     SetupPageSeeder::class,
        //     VirtualApiSeeder::class,
        //     SetupEmailSeeder::class,
        //     MerchantConfigurationSeeder::class,
        //     ModuleSettingSeeder::class,
        //     GatewayApiSeeder::class,
        //     //user
        //     UserSeeder::class,
        //     UserWalletSeeder::class,
        //     //merchant
        //     MerchantSeeder::class,
        //     MerchantWalletSeeder::class,
        //     ApiCredentialsSeeder::class,
        //     MerchantSandboxWalletSeeder::class,
        //     //Agent
        //     AgentSeeder::class,
        //     AgentWalletSeeder::class,
        //     GiftCardApiSeeder::class,
        //     ExchangeRateSeeder::class,
        //     UtilityPaymentApiSeeder::class,
        //     ReloadlyTopUpSeeder::class,
        //     CountryRestrictionSeeder::class,
        // ]);


        //fresh
        $this->call([
            AdminSeeder::class,
            RoleSeeder::class,
            TransactionSettingSeeder::class,
            CurrencySeeder::class,
            FreshBasicSettingsSeeder::class,
            BillPayCategorySeeder::class,
            TopupSeeder::class,
            LanguageSeeder::class,
            PaymentGatewaySeeder::class,
            SetupSeoSeeder::class,
            AppSettingsSeeder::class,
            OnboardScreenSeeder::class,
            SiteSectionsSeeder::class,
            SetupKycSeeder::class,
            FreshExtensionSeeder::class,
            BlogSeeder::class,
            BankTransfer::class,
            CashPickup::class,
            ReceiverCountry::class,
            AdminHasRoleSeeder::class,
            SetupPageSeeder::class,
            VirtualApiSeeder::class,
            MerchantConfigurationSeeder::class,
            ModuleSettingSeeder::class,
            GatewayApiSeeder::class,
            //merchant
            MerchantSeeder::class,
            MerchantWalletSeeder::class,
            ApiCredentialsSeeder::class,
            MerchantSandboxWalletSeeder::class,
            GiftCardApiSeeder::class,
            ExchangeRateSeeder::class,
            UtilityPaymentApiSeeder::class,
            ReloadlyTopUpSeeder::class,
            UpdateEnvData::class,
            CountryRestrictionSeeder::class,
        ]);
    }
}
