<?php

use App\Constants\GlobalConst;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\Merchant\Auth\LoginController;
use App\Http\Controllers\Api\Merchant\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Merchant\AuthorizationController;
use App\Http\Controllers\Api\Merchant\DeveloperApiController;
use App\Http\Controllers\Api\Merchant\GatewaySettingController;
use App\Http\Controllers\Api\Merchant\MoneyOutController;
use App\Http\Controllers\Api\Merchant\PaymentLinkController;
use App\Http\Controllers\Api\Merchant\UserController;
use App\Http\Controllers\Api\Merchant\ReceiveMoneyController;
use App\Http\Controllers\Api\Merchant\SecurityController;
use App\Http\Controllers\Api\Merchant\TransactionController;
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
Route::controller(AppSettingsController::class)->prefix("app-settings")->group(function(){
    Route::get('/','appSettings');
    Route::get('languages','languages');
});
Route::prefix('merchant')->group(function(){
    Route::get('get/basic/data', function() {
        $basic_settings = BasicSettingsProvider::get();
        $user_kyc = SetupKyc::merchantKyc()->first();
        $data =[
            'email_verification' => $basic_settings->email_verification,
            'kyc_verification' => $basic_settings->kyc_verification,
            'mobile_code' => getDialCode(),
            'register_kyc_fields' =>$user_kyc,
            'countries' => freedom_countries(GlobalConst::MERCHANT)
        ];
        $message =  ['success'=>[__('Basic information fetch successfully')]];
        return Helpers::success($data,$message);
    });
     //email verify before register
     Route::prefix('register')->middleware(['merchant.registration.permission'])->group(function(){
        Route::post('check/exist',[AuthorizationController::class,'checkExist']);
        Route::post('send/otp', [AuthorizationController::class,'sendEmailOtp']);
        Route::post('verify/otp',[AuthorizationController::class,"verifyEmailOtp"]);
        Route::post('resend/otp',[AuthorizationController::class,"resendEmailOtp"]);
    });
    Route::post('login',[LoginController::class,'login']);
    Route::post('register',[LoginController::class,'register'])->middleware(['merchant.registration.permission']);
    //forget password for email
    Route::prefix('forget')->group(function(){
        Route::post('password', [ForgotPasswordController::class,'sendCode']);
        Route::post('verify/otp', [ForgotPasswordController::class,'verifyCode']);
        Route::post('reset/password', [ForgotPasswordController::class,'resetPassword']);
    });

    Route::middleware(['merchant.api'])->group(function(){
        Route::get('logout', [LoginController::class,'logout']);
        Route::post('google/2fa/verify', [SecurityController::class,'verifyGoogle2Fa']);
        //pusher
         Route::get('pusher/beams-auth',[AuthorizationController::class,'pusherBeamsAuth'])->withoutMiddleware(['api','merchant.api']);
        //pusher
        //account re-verifications
        Route::post('send-code', [AuthorizationController::class,'sendMailCode']);
        Route::post('email-verify', [AuthorizationController::class,'mailVerify']);
        Route::middleware(['CheckStatusApiMerchant','merchant.google.two.factor.api'])->group(function () {
            Route::get('dashboard', [UserController::class,'home']);
            Route::get('profile', [UserController::class,'profile']);
            Route::post('profile/update', [UserController::class,'profileUpdate'])->middleware('app.mode.api');
            Route::post('password/update', [UserController::class,'passwordUpdate'])->middleware('app.mode.api');
            Route::post('delete/account', [UserController::class,'deleteAccount'])->middleware('app.mode.api');
            Route::get('notifications', [UserController::class,'notifications']);
            Route::get('kyc', [AuthorizationController::class,'showKycFrom']);
            Route::post('kyc/submit', [AuthorizationController::class,'kycSubmit']);
            //Receive Money
            Route::controller(ReceiveMoneyController::class)->prefix('receive-money')->group(function(){
                Route::get('/','index');
            });

             //Money Out
            Route::controller(MoneyOutController::class)->prefix('withdraw')->group(function(){
                Route::get('info','moneyOutInfo');
                Route::post('insert','moneyOutInsert');
                Route::post('manual/confirmed','moneyOutConfirmed')->name('merchant.api.withdraw.manual.confirmed');
                Route::post('automatic/confirmed','confirmMoneyOutAutomatic')->name('merchant.api.withdraw.automatic.confirmed');
                 //get flutterWave banks
                 Route::get('get/flutterwave/banks','getBanks');
            });
            // Payment Link
            Route::controller(PaymentLinkController::class)->prefix('payment-links/')->group(function(){
                Route::get('/', 'index');
                Route::post('/store', 'store')->middleware('api.kyc');
                Route::get('/edit', 'edit');
                Route::post('/update', 'update')->middleware('api.kyc');
                Route::post('/status', 'status')->middleware('api.kyc');
            });
             //transactions
            Route::controller(TransactionController::class)->prefix("transactions")->group(function(){
                Route::get('/{slug?}','index');

            });
             //google-2fa
            Route::controller(SecurityController::class)->prefix("security")->group(function(){
                Route::get('google/2fa/status','google2FA');

            });
             //merchant developer api
            Route::controller(DeveloperApiController::class)->prefix('developer/api')->group(function(){
                Route::get('/','index');
                Route::post('key/generate','generateApiKeys')->middleware(['app.mode.api','api.kyc']);
                Route::post('mode/update','updateMode')->middleware(['app.mode.api','api.kyc']);
                Route::post('key/delete','deleteKys')->middleware(['app.mode.api','api.kyc']);
            });
            //merchant gateway settings
            Route::controller(GatewaySettingController::class)->prefix('gateway-settings')->group(function(){
                Route::get('/','index');
                Route::post('update/wallet/status','updateWalletStatus')->middleware('app.mode.api');
                Route::post('update/virtual/card/status','updateVirtualCardStatus')->middleware('app.mode.api');
                Route::post('update/master/card/status','updateMasterCardStatus')->middleware('app.mode.api');
                Route::post('update/master/card/credentials','updateMasterCardCredentials')->middleware('app.mode.api');
            });

        });

    });

});
