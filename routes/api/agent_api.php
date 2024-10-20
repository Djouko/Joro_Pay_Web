<?php

use App\Constants\GlobalConst;
use App\Http\Controllers\Api\Agent\AddMoneyController;
use App\Http\Controllers\Api\Agent\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\Agent\Auth\LoginController;
use App\Http\Controllers\Api\Agent\AuthorizationController;
use App\Http\Controllers\Api\Agent\BillPayController;
use App\Http\Controllers\Api\Agent\MobileTopupController;
use App\Http\Controllers\Api\Agent\MoneyInController;
use App\Http\Controllers\Api\Agent\ReceiveMoneyController;
use App\Http\Controllers\Api\Agent\RecipientController;
use App\Http\Controllers\Api\Agent\RemittanceController;
use App\Http\Controllers\Api\Agent\SecurityController;
use App\Http\Controllers\Api\Agent\SendMoneyController;
use App\Http\Controllers\Api\Agent\TransactionController;
use App\Http\Controllers\Api\Agent\UserController;
use App\Http\Controllers\Api\Agent\WithdrawController;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\SetupKyc;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    $message =  ['success'=>['Clear cache successfully']];
    return Helpers::onlysuccess($message);
});
Route::controller(AppSettingsController::class)->prefix("app-settings")->group(function(){
    Route::get('/','appSettings');
    Route::get('languages','languages');
});
Route::prefix('agent')->group(function(){
    Route::get('get/basic/data', function() {
        $basic_settings = BasicSettingsProvider::get();
        $user_kyc = SetupKyc::agentKyc()->first();
        $data =[
            'email_verification' => $basic_settings->email_verification,
            'kyc_verification' => $basic_settings->kyc_verification,
            'mobile_code' => getDialCode(),
            'register_kyc_fields' =>$user_kyc,
            'countries' => freedom_countries(GlobalConst::AGENT)
        ];
        $message =  ['success'=>[__('Basic information fetch successfully')]];
        return Helpers::success($data,$message);
    });
    Route::prefix('register')->middleware(['agent.registration.permission'])->group(function(){
        Route::post('check/exist',[AuthorizationController::class,'checkExist']);
        Route::post('send/otp', [AuthorizationController::class,'sendEmailOtp']);
        Route::post('verify/otp',[AuthorizationController::class,"verifyEmailOtp"]);
        Route::post('resend/otp',[AuthorizationController::class,"resendEmailOtp"]);
    });
    Route::post('login',[LoginController::class,'login']);
    Route::post('register',[LoginController::class,'register'])->middleware(['agent.registration.permission']);
    //forget password for email
    Route::prefix('forget')->group(function(){
        Route::post('password', [ForgotPasswordController::class,'sendCode']);
        Route::post('verify/otp', [ForgotPasswordController::class,'verifyCode']);
        Route::post('reset/password', [ForgotPasswordController::class,'resetPassword']);
    });
     //account re-verifications
     Route::middleware(['agent.api'])->group(function(){
        Route::post('send-code', [AuthorizationController::class,'sendMailCode']);
        Route::post('email-verify', [AuthorizationController::class,'mailVerify']);
        Route::post('google-2fa/otp/verify', [AuthorizationController::class,'verify2FACode']);
    });

    Route::middleware(['agent.api'])->group(function(){
        Route::get('logout', [LoginController::class,'logout']);
        Route::get('kyc', [AuthorizationController::class,'showKycFrom']);
        Route::post('kyc/submit', [AuthorizationController::class,'kycSubmit']);
        //pusher
        Route::get('pusher/beams-auth',[AuthorizationController::class,'pusherBeamsAuth'])->withoutMiddleware(['api','agent.api']);
        //pusher
        Route::middleware(['CheckStatusApiAgent','agent.google.two.factor.api'])->group(function () {
            Route::get('dashboard', [UserController::class,'home']);
            Route::get('profile', [UserController::class,'profile']);
            Route::post('profile/update', [UserController::class,'profileUpdate'])->middleware('app.mode.api');
            Route::post('password/update', [UserController::class,'passwordUpdate'])->middleware('app.mode.api');
            Route::post('delete/account', [UserController::class,'deleteAccount'])->middleware('app.mode.api');
            Route::get('notifications', [UserController::class,'notifications']);
             //add money
            Route::controller(AddMoneyController::class)->prefix("add-money")->group(function(){
                Route::get('/information','addMoneyInformation');
                Route::post('submit-data','submitData');

                //PayPal
                Route::get('success/paypal/response/{gateway}','success')->name('agent.api.payment.success')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                Route::get("cancel/paypal/response/{gateway}",'cancel')->name('agent.api.payment.cancel')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                //Stripe
                Route::get('stripe/payment/success/{trx}','stripePaymentSuccess')->name('agent.api.stripe.payment.success')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                Route::get('/flutterwave/callback', 'flutterwaveCallback')->name('agent.api.flutterwave.callback')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                //sslcommerz
                Route::post('sslcommerz/success','sllCommerzSuccess')->name('agent.api.add.money.ssl.success')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                Route::post('sslcommerz/fail','sllCommerzFails')->name('agent.api.add.money.ssl.fail')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                Route::post('sslcommerz/cancel','sllCommerzCancel')->name('agent.api.add.money.ssl.cancel')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);

                //coingate
                Route::match(['get','post'],'coingate/success/response/{gateway}','coinGateSuccess')->name('agent.api.coingate.payment.success')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);
                Route::match(['get','post'],"coingate/cancel/response/{gateway}",'coinGateCancel')->name('agent.api.coingate.payment.cancel')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','agent.google.two.factor.api']);

                Route::prefix('payment')->name('api.agent.add.money.payment.')->group(function() {
                    Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm');
                });
                //manual gateway
                Route::post('manual/payment/confirmed','manualPaymentConfirmedApi')->name('agent.api.manual.payment.confirmed');

                //redirect with Btn Pay
                Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('api.agent.add.money.payment.btn.pay')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','verification.guard.api','agent.google.two.factor.api']);

                // Global Gateway Response Routes
                Route::get('success/response/{gateway}','successGlobal')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','verification.guard.api','agent.google.two.factor.api'])->name("api.agent.add.money.payment.global.success");
                Route::get("cancel/response/{gateway}",'cancelGlobal')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','verification.guard.api','agent.google.two.factor.api'])->name("api.agent.add.money.payment.global.cancel");

                // POST Route For Unauthenticated Request
                Route::post('success/response/{gateway}', 'postSuccess')->name('api.agent.add.money.payment.global.success')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','verification.guard.api','agent.google.two.factor.api']);
                Route::post('cancel/response/{gateway}', 'postCancel')->name('api.agent.add.money.payment.global.cancel')->withoutMiddleware(['auth:api','agent.api','CheckStatusApiAgent','verification.guard.api','agent.google.two.factor.api']);

            });
            //Receive Money
            Route::controller(ReceiveMoneyController::class)->prefix('receive-money')->group(function(){
                Route::get('/','index');
            });
            //Send Money
            Route::controller(SendMoneyController::class)->prefix('send-money')->group(function(){
                Route::get('info','sendMoneyInfo');
                Route::post('exist','checkUser');
                Route::post('qr/scan','qrScan');
                Route::post('confirmed','confirmedSendMoney')->middleware(['api.kyc']);
            });
            //Money In
            Route::controller(MoneyInController::class)->prefix('money-in')->group(function(){
                Route::get('info','MoneyInInfo');
                Route::post('exist','checkUser');
                Route::post('qr/scan','qrScan');
                Route::post('confirmed','confirmedMoneyIn')->middleware(['api.kyc']);
            });
             //Withdraw Controller
            Route::controller(WithdrawController::class)->prefix('withdraw')->group(function(){
                Route::get('info','withdrawInfo');
                Route::post('insert','withdrawInsert')->middleware(['api.kyc']);
                Route::post('manual/confirmed','withdrawConfirmed')->name('agent.withdraw.manual.confirmed')->middleware(['api.kyc']);
                Route::post('automatic/confirmed','confirmWithdrawAutomatic')->name('agent.withdraw.automatic.confirmed')->middleware(['api.kyc']);
                //get flutterwave banks
                Route::get('get/flutterwave/banks','getBanks')->middleware(['api.kyc']);
            });
             //Bill Pay
            Route::controller(BillPayController::class)->prefix('bill-pay')->group(function(){
                Route::get('info','billPayInfo');
                Route::post('confirmed','billPayConfirmed')->middleware(['api.kyc']);
            });
             //mobile top up
            Route::controller(MobileTopupController::class)->prefix('mobile-topup')->group(function(){
                Route::get('info','topUpInfo');
                Route::post('confirmed','topUpConfirmed')->middleware(['api.kyc']);
                //automatic method
                Route::prefix('automatic')->group(function(){
                    Route::get('check-operator','checkOperator');
                    Route::post('pay','payAutomatic')->middleware('api.kyc');
                });
            });
             //Saved Recipient
            Route::controller(RecipientController::class)->prefix('recipient')->group(function(){
                Route::get('dynamic/fields','dynamicFields');
                Route::get('save/info','saveRecipientInfo');
                Route::post('check/user','checkUser');
                //sender recipient
                Route::prefix('sender')->group(function(){
                    Route::get('list','recipientList');
                    Route::post('store','storeRecipient')->middleware(['api.kyc']);
                    Route::get('edit','editRecipient');
                    Route::post('update','updateRecipient')->middleware(['api.kyc']);
                    Route::post('delete','deleteRecipient')->middleware(['api.kyc']);
                });
                //receiver Recipient
                Route::prefix('receiver')->group(function(){
                    Route::get('list','recipientListReceiver');
                    Route::post('store','storeRecipientReceiver')->middleware(['api.kyc']);
                    Route::get('edit','editRecipientReceiver');
                    Route::post('update','updateRecipientReceiver')->middleware(['api.kyc']);
                    Route::post('delete','deleteRecipientReceiver')->middleware(['api.kyc']);
                });
            });
             //Remittance
            Route::controller(RemittanceController::class)->prefix('remittance')->group(function(){
                Route::get('info','remittanceInfo');
                Route::post('confirmed','confirmed')->middleware(['api.kyc']);
                //for filters
                Route::post('get/recipient/sender','getRecipientSender')->middleware(['api.kyc']);
                Route::post('get/recipient/receiver','getRecipientReceiver')->middleware(['api.kyc']);
            });
             //transactions
            Route::controller(TransactionController::class)->prefix("transactions")->group(function(){
                Route::get('/{slug?}','index');
            });
              //google-2fa
              Route::controller(SecurityController::class)->prefix("security")->group(function(){
                Route::get('/google-2fa', 'google2FA');
                Route::post('/google-2fa/status/update', 'google2FAStatusUpdate')->middleware('app.mode.api');

            });


        });

    });

});
