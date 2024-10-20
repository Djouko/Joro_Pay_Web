<?php

use App\Constants\GlobalConst;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\User\AddMoneyController;
use App\Http\Controllers\Api\User\AgentMoneyOutController;
use App\Http\Controllers\Api\User\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\User\Auth\LoginController;
use App\Http\Controllers\Api\User\AuthorizationController;
use App\Http\Controllers\Api\User\BillPayController;
use App\Http\Controllers\Api\User\GiftCardController;
use App\Http\Controllers\Api\User\MakePaymentController;
use App\Http\Controllers\Api\User\MobileTopupController;
use App\Http\Controllers\Api\User\MoneyOutController;
use App\Http\Controllers\Api\User\PaymentLinkController;
use App\Http\Controllers\Api\User\ReceiveMoneyController;
use App\Http\Controllers\Api\User\RecipientController;
use App\Http\Controllers\Api\User\RemittanceController;
use App\Http\Controllers\Api\User\SecurityController;
use App\Http\Controllers\Api\User\SendMoneyController;
use App\Http\Controllers\Api\User\StripeVirtualController;
use App\Http\Controllers\Api\User\SudoVirtualCardController;
use App\Http\Controllers\Api\User\TransactionController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\VirtualCardController;
use App\Http\Controllers\Api\User\RequestMoneyController;
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
    $message =  ['success'=>[__('Clear cache successfully')]];
    return Helpers::onlysuccess($message);
});
Route::get('get/basic/data', function() {
    $basic_settings = BasicSettingsProvider::get();
    $user_kyc = SetupKyc::userKyc()->first();
    $data =[
        'email_verification' => $basic_settings->email_verification,
        'kyc_verification' => $basic_settings->kyc_verification,
        'mobile_code' => getDialCode(),
        'register_kyc_fields' =>$user_kyc,
        'countries' => freedom_countries(GlobalConst::USER)
    ];
    $message =  ['success'=>[__('Basic information fetch successfully')]];
    return Helpers::success($data,$message);
});
Route::controller(AppSettingsController::class)->prefix("app-settings")->group(function(){
    Route::get('/','appSettings');
    Route::get('languages','languages');
});
Route::controller(AddMoneyController::class)->prefix("add-money")->group(function(){
    Route::get('success/response/paypal/{gateway}','success')->name('api.payment.success');
    Route::get("cancel/response/paypal/{gateway}",'cancel')->name('api.payment.cancel');
    Route::get('/flutterwave/callback', 'flutterwaveCallback')->name('api.flutterwave.callback');
    //Stripe
    Route::get('stripe/payment/success/{trx}','stripePaymentSuccess')->name('api.stripe.payment.success');
    //coingate
    Route::match(['get','post'],'coingate/success/response/{gateway}','coinGateSuccess')->name('api.coingate.payment.success');
    Route::match(['get','post'],"coingate/cancel/response/{gateway}",'coinGateCancel')->name('api.coingate.payment.cancel');
});

Route::prefix('user')->group(function(){
    //email verify before register
    Route::prefix('register')->middleware(['user.registration.permission'])->group(function(){
        Route::post('check/exist',[AuthorizationController::class,'checkExist']);
        Route::post('send/otp', [AuthorizationController::class,'sendEmailOtp']);
        Route::post('verify/otp',[AuthorizationController::class,"verifyEmailOtp"]);
        Route::post('resend/otp',[AuthorizationController::class,"resendEmailOtp"]);
    });

    Route::post('register',[LoginController::class,'register'])->middleware(['user.registration.permission']);
    Route::post('login',[LoginController::class,'login']);

    //forget password for email
    Route::prefix('forget')->group(function(){
        Route::post('password', [ForgotPasswordController::class,'sendCode']);
        Route::post('verify/otp', [ForgotPasswordController::class,'verifyCode']);
        Route::post('reset/password', [ForgotPasswordController::class,'resetPassword']);
    });
    //account re-verifications
    Route::middleware(['auth.api'])->group(function(){
        Route::post('send-code', [AuthorizationController::class,'sendMailCode']);
        Route::post('email-verify', [AuthorizationController::class,'mailVerify']);
    });

    Route::middleware(['auth.api','verification.guard.api'])->group(function(){
        Route::get('logout', [LoginController::class,'logout']);
        Route::get('kyc', [AuthorizationController::class,'showKycFrom']);
        Route::post('kyc/submit', [AuthorizationController::class,'kycSubmit']);
        //pusher
        Route::get('pusher/beams-auth',[AuthorizationController::class,'pusherBeamsAuth'])->withoutMiddleware(['api','auth.api','verification.guard.api','CheckStatusApiUser','user.google.two.factor.api']);
        //pusher
        Route::post('google/2fa/verify', [SecurityController::class,'verifyGoogle2Fa']);
        Route::middleware(['CheckStatusApiUser','user.google.two.factor.api'])->group(function () {
            Route::get('dashboard', [UserController::class,'home']);
            Route::get('profile', [UserController::class,'profile']);
            Route::post('profile/update', [UserController::class,'profileUpdate'])->middleware('app.mode.api');
            Route::post('password/update', [UserController::class,'passwordUpdate'])->middleware('app.mode.api');
            Route::post('delete/account', [UserController::class,'deleteAccount'])->middleware('app.mode.api');
            Route::get('notifications', [UserController::class,'notifications']);

             //virtual card flutterwave
            Route::middleware('virtual_card_method:flutterwave')->group(function(){
                Route::controller(VirtualCardController::class)->prefix('my-card')->group(function(){
                    Route::get('/','index');
                    Route::get('charges','charges');
                    Route::post('create','cardBuy')->middleware('api.kyc');
                    Route::post('fund','cardFundConfirm')->middleware('api.kyc');
                    Route::get('details','cardDetails');
                    Route::get('transaction','cardTransaction');
                    Route::post('block','cardBlock');
                    Route::post('unblock','cardUnBlock');
                    Route::post('make-remove/default','makeDefaultOrRemove');
                });
            });
             //virtual card sudo
            Route::middleware('virtual_card_method:sudo')->group(function(){
                Route::controller(SudoVirtualCardController::class)->prefix('my-card/sudo')->group(function(){
                    Route::get('/','index');
                    Route::get('charges','charges');
                    Route::get('details','cardDetails');
                    Route::post('create','cardBuy')->middleware('api.kyc');
                    Route::post('fund','cardFundConfirm')->middleware('api.kyc');
                    Route::get('details','cardDetails');
                    Route::get('transaction','cardTransaction');
                    Route::post('block','cardBlock');
                    Route::post('unblock','cardUnBlock');
                    Route::post('make-remove/default','makeDefaultOrRemove');
                });
            });
            //virtual card stripe
            Route::middleware('virtual_card_method:stripe')->group(function(){
                Route::controller(StripeVirtualController::class)->prefix('my-card/stripe')->group(function(){
                    Route::get('/','index');
                    Route::get('details','cardDetails');
                    Route::post('create','cardBuy')->middleware('api.kyc');
                    Route::get('transaction','cardTransaction');
                    Route::post('inactive','cardInactive');
                    Route::post('active','cardActive');
                    Route::post('get/sensitive/data','getSensitiveData');
                    Route::post('make-remove/default','makeDefaultOrRemove');
                });
            });

             //add money
            Route::controller(AddMoneyController::class)->prefix("add-money")->group(function(){
                Route::get('/information','addMoneyInformation');
                Route::post('submit-data','submitData')->middleware('api.kyc');
                //manual gateway
                Route::post('manual/payment/confirmed','manualPaymentConfirmedApi')->name('api.manual.payment.confirmed')->middleware('api.kyc');

                Route::prefix('payment')->name('api.user.add.money.payment.')->group(function() {
                    Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm')->middleware('api.kyc');
                });

                //redirect with Btn Pay
                Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('api.user.add.money.payment.btn.pay')->withoutMiddleware(['auth:api','auth.api','CheckStatusApiUser','verification.guard.api','user.google.two.factor.api']);

                // Global Gateway Response Routes
                Route::get('success/response/{gateway}','successGlobal')->withoutMiddleware(['auth:api','auth.api','CheckStatusApiUser','verification.guard.api','user.google.two.factor.api'])->name("api.user.add.money.payment.global.success");
                Route::get("cancel/response/{gateway}",'cancelGlobal')->withoutMiddleware(['auth:api','auth.api','CheckStatusApiUser','verification.guard.api','user.google.two.factor.api'])->name("api.user.add.money.payment.global.cancel");

                // POST Route For Unauthenticated Request
                Route::post('success/response/{gateway}', 'postSuccess')->name('api.user.add.money.payment.global.success')->withoutMiddleware(['auth:api','auth.api','CheckStatusApiUser','verification.guard.api','user.google.two.factor.api']);
                Route::post('cancel/response/{gateway}', 'postCancel')->name('api.user.add.money.payment.global.cancel')->withoutMiddleware(['auth:api','auth.api','CheckStatusApiUser','verification.guard.api','user.google.two.factor.api']);

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
                Route::post('confirmed','confirmedSendMoney');
            });
             //Agent Money Out
            Route::controller(AgentMoneyOutController::class)->prefix('money-out')->group(function(){
                Route::get('info','index');
                Route::post('confirmed','confirmed')->middleware('api.kyc');
                Route::post('check/agent','checkAgent');
                Route::post('qr/scan','qrScan');
            });

            //request Money
            Route::controller(RequestMoneyController::class)->prefix("request-money")->group(function(){
                Route::get('/','index')->name('index');
                Route::post('submit','submit')->name('submit')->middleware('api.kyc');
                Route::post('check/user','checkUser');
                Route::post('qr/scan','qrScan');
                Route::prefix("logs")->name("log.")->middleware("api.kyc")->group(function(){
                    Route::get('/','logLists')->name('list');
                    Route::post('approve','approved')->name('approve')->middleware('api.kyc');
                    Route::post('reject','rejected')->name('reject')->middleware('api.kyc');
                });
            });
            // Payment Link
            Route::controller(PaymentLinkController::class)->prefix('payment-links/')->group(function(){
                Route::get('/', 'index');
                Route::post('/store', 'store');
                Route::get('/edit', 'edit');
                Route::post('/update', 'update');
                Route::post('/status', 'status');
            });

            //Withdraw Money
            Route::controller(MoneyOutController::class)->prefix('withdraw')->group(function(){
                Route::get('info','moneyOutInfo');
                Route::post('insert','moneyOutInsert')->middleware('api.kyc');
                Route::post('manual/confirmed','moneyOutConfirmed')->name('api.withdraw.manual.confirmed')->middleware('api.kyc');
                Route::post('automatic/confirmed','confirmMoneyOutAutomatic')->name('api.withdraw.automatic.confirmed')->middleware('api.kyc');
               //get flutterWave banks
               Route::get('get/flutterwave/banks','getBanks');
            });
             //Make Payment
             Route::controller(MakePaymentController::class)->prefix('make-payment')->group(function(){
                Route::get('info','makePaymentInfo');
                Route::post('check/merchant','checkMerchant');
                Route::post('merchants/scan','qrScan');
                Route::post('confirmed','confirmedPayment')->middleware('api.kyc');
            });
             //Bill Pay
            Route::controller(BillPayController::class)->prefix('bill-pay')->group(function(){
                Route::get('info','billPayInfo');
                Route::post('confirmed','billPayConfirmed')->middleware('api.kyc');
            });
             //mobile top up
            Route::controller(MobileTopupController::class)->prefix('mobile-topup')->group(function(){
                Route::get('info','topUpInfo');
                Route::post('confirmed','topUpConfirmed')->middleware('api.kyc');
                //automatic method
                Route::prefix('automatic')->group(function(){
                    Route::get('check-operator','checkOperator');
                    Route::post('pay','payAutomatic')->middleware('api.kyc');
                });
            });
            //gift card
            Route::controller(GiftCardController::class)->prefix('gift-card')->group(function(){
                Route::get('/', 'index');
                Route::get('all', 'allGiftCard');
                Route::get('search/', 'searchGiftCard');
                Route::get('details', 'giftCardDetails');
                Route::post('order', 'orderPlace')->middleware('api.kyc');
            });
            //Saved Recipient
            Route::controller(RecipientController::class)->prefix('recipient')->group(function(){
                Route::get('list','recipientList');
                Route::get('save/info','saveRecipientInfo');
                Route::get('dynamic/fields','dynamicFields');
                Route::post('check/user','checkUser');
                Route::post('store','storeRecipient');
                Route::get('edit','editRecipient');
                Route::post('update','updateRecipient');
                Route::post('delete','deleteRecipient');
            });
             //Remittance
            Route::controller(RemittanceController::class)->prefix('remittance')->group(function(){
                Route::get('info','remittanceInfo');
                Route::post('confirmed','confirmed')->middleware('api.kyc');
                //for filters
                Route::post('get/recipient','getRecipient');
                // Route::post('get/recipient/transaction/type','getRecipientByTransType');
            });
             //transactions
            Route::controller(TransactionController::class)->prefix("transactions")->group(function(){
                Route::get('/{slug?}','index');
            });
              //google-2fa
              Route::controller(SecurityController::class)->prefix("security")->group(function(){
                Route::get('google/2fa/status','google2FA');
            });

        });

    });

});
