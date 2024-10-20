<?php
namespace App\Constants;

use App\Models\AgentWallet;
use App\Models\Merchants\MerchantWallet;
use App\Models\UserWallet;
use Illuminate\Support\Str;

class PaymentGatewayConst {

    const AUTOMATIC = "AUTOMATIC";
    const MANUAL    = "MANUAL";
    const ADDMONEY  = "Add Money";
    const MONEYOUT  = "Money Out";
    const ACTIVE    =  true;

    const FIAT                      = "FIAT";
    const CRYPTO                    = "CRYPTO";
    const CRYPTO_NATIVE             = "CRYPTO_NATIVE";
    const ASSET_TYPE_WALLET         = "WALLET";
    const CALLBACK_HANDLE_INTERNAL  = "CALLBACK_HANDLE_INTERNAL";

    const NOT_USED  = "NOT-USED";
    const USED      = "USED";
    const SENT      = "SENT";

    const LINK_TYPE_PAY = 'pay';
    const LINK_TYPE_SUB = 'sub';
    const TYPE_GATEWAY_PAYMENT = 'payment_gateway';
    const TYPE_CARD_PAYMENT = 'card_payment';
    const TYPE_WALLET_SYSTEM = 'wallet_payment';

    const TYPEADDMONEY      = "ADD-MONEY";
    const TYPEMONEYOUT      = "MONEY-OUT";
    const TYPEWITHDRAW      = "WITHDRAW";
    const TYPECOMMISSION    = "COMMISSION";
    const TYPEBONUS         = "BONUS";
    const TYPETRANSFERMONEY = "TRANSFER-MONEY";
    const MONEYIN           = "MONEY-IN";
    const SENDREMITTANCE    = "REMITTANCE";
    const RECEIVEREMITTANCE = "RECEIVE-REMITTANCE";
    const TYPEMONEYEXCHANGE = "MONEY-EXCHANGE";
    const BILLPAY           = "BILL-PAY";
    const MOBILETOPUP       = "MOBILE-TOPUP";
    const VIRTUALCARD       = "VIRTUAL-CARD";
    const CARDBUY           = "CARD-BUY";
    const CARDFUND          = "CARD-FUND";
    const REQUESTMONEY      = "REQUEST-MONEY";
    const TYPEPAYLINK            = "PAY-LINK";
    const PAYMENTPAYLINK            = "PAYMENT_PAY-LINK";
    const TYPEADDSUBTRACTBALANCE = "ADD-SUBTRACT-BALANCE";
    const TYPEMAKEPAYMENT   = "MAKE-PAYMENT";
    const AGENTMONEYOUT     = "AGENT-MONEY-OUT";
    const PROFITABLELOGS        = "PROFITABLE-LOGS";
    const GIFTCARD          = "GIFT-CARD";


    const STATUSSUCCESS     = 1;
    const STATUSPENDING     = 2;
    const STATUSHOLD        = 3;
    const STATUSREJECTED    = 4;
    const STATUSWAITING     = 5;
    const STATUSFAILD       = 6;
    const STATUSPROCESSING  = 7;

    const PAYPAL                = 'paypal';
    const STRIPE                = 'stripe';
    const MANUA_GATEWAY         = 'manual';
    const FLUTTER_WAVE          = 'flutterwave';
    const RAZORPAY              = 'razorpay';
    const PAGADITO              = 'pagadito';
    const SSLCOMMERZ            = 'sslcommerz';
    const COINGATE              = 'coingate';
    const TATUM                 = 'tatum';
    const PERFECT_MONEY         = 'perfect-money';


    const SEND = "SEND";
    const RECEIVED = "RECEIVED";
    const PENDING = "PENDING";
    const REJECTED = "REJECTED";
    const CREATED = "CREATED";
    const SUCCESS = "SUCCESS";
    const EXPIRED = "EXPIRED";

    const ENV_SANDBOX       = "SANDBOX";
    const ENV_PRODUCTION    = "PRODUCTION";

    //merchant payment type
    const MERCHANTPAYMENT       ="MERCHANT-PAYMENT";
    const WALLET                = "WALLET";
    const VIRTUAL               = "VIRTUAL-CARD";
    const MASTER                = "MASTER-CARD";



    public static function add_money_slug() {
        return Str::slug(self::ADDMONEY);
    }

    public static function paylink_slug(){
        return Str::slug(self::TYPEPAYLINK);
    }

    public static function money_out_slug() {
        return Str::slug(self::MONEYOUT);
    }
    const REDIRECT_USING_HTML_FORM = "REDIRECT_USING_HTML_FORM";

    public static function register($alias = null) {
        $gateway_alias  = [
            self::PAYPAL => "paypalInit",
            self::STRIPE => "stripeInit",
            self::MANUA_GATEWAY => "manualInit",
            self::FLUTTER_WAVE => 'flutterwaveInit',
            self::RAZORPAY => 'razorInit',
            self::PAGADITO => 'pagaditoInit',
            self::SSLCOMMERZ => 'sslcommerzInit',
            self::COINGATE  => 'coingateInit',
            self::TATUM         => 'tatumInit',
            self::PERFECT_MONEY => 'perfectMoneyInit'
        ];

        if($alias == null) {
            return $gateway_alias;
        }

        if(array_key_exists($alias,$gateway_alias)) {
            return $gateway_alias[$alias];
        }
        return "init";
    }
    const APP       = "APP";
    public static function apiAuthenticateGuard() {
            return [
                'api'   => 'web',
                'agent_api'   => 'agent',
            ];
    }
    public static function registerWallet() {
        return [
            'web'           => UserWallet::class,
            'api'           => UserWallet::class,
            'agent'         => AgentWallet::class,
            'agent_api'     => AgentWallet::class,
            'merchant'      => MerchantWallet::class,
        ];
    }
    public static function registerGatewayRecognization() {
        return [
            'isCoinGate'    => self::COINGATE,
            'isTatum'       => self::TATUM,
            'isRazorpay'        => self::RAZORPAY,
            'isPerfectMoney'    => self::PERFECT_MONEY,
        ];
    }

    public static function registerRedirection() {
        return [
            'web'       => [
                'return_url'    => 'user.add.money.payment.global.success',
                'cancel_url'    => 'user.add.money.payment.global.cancel',
                'callback_url'  => 'user.add.money.payment.callback',
                'redirect_form' => 'user.add.money.payment.redirect.form',
                'btn_pay'       => 'user.add.money.payment.btn.pay',
            ],
            'agent'       => [
                'return_url'    => 'agent.add.money.payment.global.success',
                'cancel_url'    => 'agent.add.money.payment.global.cancel',
                'callback_url'  => 'user.add.money.payment.callback',
                'redirect_form' => 'agent.add.money.payment.redirect.form',
                'btn_pay'       => 'agent.add.money.payment.btn.pay',
            ],
            'api'       => [
                'return_url'    => 'api.user.add.money.payment.global.success',
                'cancel_url'    => 'api.user.add.money.payment.global.cancel',
                'callback_url'  => 'user.add.money.payment.callback',
                'redirect_form' => 'user.add.money.payment.redirect.form',
                'btn_pay'       => 'api.user.add.money.payment.btn.pay',
            ],
            'agent_api'       => [
                'return_url'    => 'api.agent.add.money.payment.global.success',
                'cancel_url'    => 'api.agent.add.money.payment.global.cancel',
                'callback_url'  => 'user.add.money.payment.callback',
                'redirect_form' => 'agent.add.money.payment.redirect.form',
                'btn_pay'       => 'api.agent.add.money.payment.btn.pay',
            ],
            'pay-link'       => [
                'return_url'    => 'payment-link.gateway.payment.global.success',
                'cancel_url'    => 'payment-link.gateway.payment.global.cancel',
                'callback_url'  => 'payment-link.gateway.payment.callback',
                'redirect_form' => 'payment-link.gateway.payment.redirect.form',
                'btn_pay'       => 'payment-link.gateway.payment.btn.pay',
            ],
        ];
    }

}
