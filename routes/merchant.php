<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Providers\Admin\BasicSettingsProvider;
use App\Http\Controllers\User\WalletController;
use Pusher\PushNotifications\PushNotifications;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\DeveloperApiController;
use App\Http\Controllers\Merchant\GatewaySettingController;
use App\Http\Controllers\Merchant\MoneyOutController;
use App\Http\Controllers\Merchant\PaymentLinkController;
use App\Http\Controllers\Merchant\ProfileController;
use App\Http\Controllers\Merchant\ReceiveMoneyController;
use App\Http\Controllers\Merchant\SecurityController as MerchantSecurityController;
use App\Http\Controllers\Merchant\SupportTicketController;
use App\Http\Controllers\Merchant\TransactionController;

Route::prefix("merchant")->name("merchant.")->group(function(){
    Route::controller(DashboardController::class)->group(function(){
        Route::get('dashboard','index')->name('dashboard');
        Route::post('logout','logout')->name('logout');
    });
    //profile
    Route::controller(ProfileController::class)->prefix("profile")->name("profile.")->middleware('app.mode')->group(function(){
        Route::get('/','index')->name('index');
        Route::put('password/update','passwordUpdate')->name('password.update');
        Route::put('update','update')->name('update');
        Route::delete('delete/account','deleteAccount')->name('delete.account');
    });

     //Receive Money
    Route::middleware('module:merchant-receive-money')->group(function(){
        Route::controller(ReceiveMoneyController::class)->prefix('receive-money')->name('receive.money.')->group(function(){
            Route::get('/','index')->name('index');
        });
    });
     //Pay Link
     Route::middleware('module:merchant-pay-link')->group(function(){
        Route::controller(PaymentLinkController::class)->prefix('payment-link')->name('payment-link.')->group(function(){
            Route::get('/','index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store')->middleware('kyc.verification.guard');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update', 'update')->name('update')->middleware('kyc.verification.guard');
            Route::get('/share/{id}', 'share')->name('share');
            Route::delete('delete', 'delete')->name('delete')->middleware('kyc.verification.guard');
            Route::post('/status', 'status')->name('status')->middleware('kyc.verification.guard');
        });
    });
    Route::controller(WalletController::class)->prefix("wallets")->name("wallets.")->group(function(){
        Route::get("/","index")->name("index");
        Route::post("balance","balance")->name("balance");
    });

    //money out
    Route::middleware('module:merchant-withdraw-money')->group(function(){
        Route::controller(MoneyOutController::class)->prefix('withdraw')->name('withdraw.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('insert','paymentInsert')->name('insert')->middleware('kyc.verification.guard');
            Route::get('preview','preview')->name('preview');
            Route::post('confirm','confirmMoneyOut')->name('confirm')->middleware('kyc.verification.guard');
            //check bank validation
            Route::post('check/flutterwave/bank','checkBanks')->name('check.flutterwave.bank');
            //automatic withdraw confirmed
            Route::post('automatic/confirmed','confirmMoneyOutAutomatic')->name('confirm.automatic')->middleware('kyc.verification.guard');
        });
    });
    //transactions
    Route::controller(TransactionController::class)->prefix("transactions")->name("transactions.")->group(function(){
        Route::get('/{slug?}','index')->name('index')->whereIn('slug',['add-money','withdraw','transfer-money','money-exchange','bill-pay','mobile-topup','virtual-card','remittance','merchant-payment']);
        Route::post('search','search')->name('search');
    });
    //google-2fa
    Route::controller(MerchantSecurityController::class)->prefix("security")->name('security.')->group(function(){
        Route::get('google/2fa','google2FA')->name('google.2fa');
        Route::post('google/2fa/status/update','google2FAStatusUpdate')->name('google.2fa.status.update')->middleware('app.mode');;
    });

    //support tickets
    Route::controller(SupportTicketController::class)->prefix("support/ticket")->name("support.ticket.")->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('conversation/{encrypt_id}','conversation')->name('conversation');
        Route::post('message/send','messageSend')->name('messaage.send');
    });

    //merchant developer api
    Route::middleware('module:merchant-api-key')->group(function(){
        Route::controller(DeveloperApiController::class)->prefix('developer/api')->name('developer.api.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('generate/keys','generateApiKeys')->name('generate.keys')->middleware(['app.mode','kyc.verification.guard']);
            Route::post('mode/update','updateMode')->name('mode.update')->middleware(['app.mode','kyc.verification.guard']);
            Route::post('keys/delete','deleteKys')->name('delete.keys')->middleware(['app.mode','kyc.verification.guard']);
        });
    });
    //merchant gateway settings
    Route::middleware('module:merchant-gateway-settings')->group(function(){
        Route::controller(GatewaySettingController::class)->prefix('gateway-setting')->name('gateway.setting.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('update/wallet/status','updateWalletStatus')->name('update.wallet.status')->middleware('app.mode');
            Route::post('update/virtual/card/status','updateVirtualCardStatus')->name('update.virtual.status')->middleware('app.mode');
            Route::post('update/master/card/status','updateMasterCardStatus')->name('update.master.status')->middleware('app.mode');
            Route::post('update/master/card/credentials','updateMasterCardCredentials')->name('update.master.card.credentials')->middleware('app.mode');
        });
    });


});
Route::get('merchant/pusher/beams-auth', function (Request $request) {
    if(Auth::check() == false) {
        return response(['Inconsistent request'], 401);
    }
    $userID = userGuard()['user']->id;

    $basic_settings = BasicSettingsProvider::get();
    if(!$basic_settings) {
        return response('Basic setting not found!', 404);
    }

    $notification_config = $basic_settings->push_notification_config;

    if(!$notification_config) {
        return response('Notification configuration not found!', 404);
    }

    $instance_id    = $notification_config->instance_id ?? null;
    $primary_key    = $notification_config->primary_key ?? null;
    if($instance_id == null || $primary_key == null) {
        return response('Sorry! You have to configure first to send push notification.', 404);
    }
    $beamsClient = new PushNotifications(
        array(
            "instanceId" => $notification_config->instance_id,
            "secretKey" => $notification_config->primary_key,
        )
    );
    $publisherUserId =  make_user_id_for_pusher("merchant", $userID);
    try{
        $beamsToken = $beamsClient->generateToken($publisherUserId);
    }catch(Exception $e) {
        return response(['Server Error. Failed to generate beams token.'], 500);
    }

    return response()->json($beamsToken);
})->name('merchant.pusher.beams.auth');
