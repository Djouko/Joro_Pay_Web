<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GlobalController;
use App\Providers\Admin\BasicSettingsProvider;
use Pusher\PushNotifications\PushNotifications;
use App\Http\Controllers\Agent\BillPayController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\AddMoneyController;
use App\Http\Controllers\Agent\SecurityController;
use App\Http\Controllers\Agent\DashboardController;
use App\Http\Controllers\Agent\RemitanceController;
use App\Http\Controllers\Agent\SendMoneyController;
use App\Http\Controllers\Agent\MobileTopupController;
use App\Http\Controllers\Agent\MoneyInController;
use App\Http\Controllers\Agent\ProfitsController;
use App\Http\Controllers\Agent\TransactionController;
use App\Http\Controllers\Agent\ReceiveMoneyController;
use App\Http\Controllers\Agent\ReceiverRecipientController;
use App\Http\Controllers\Agent\SenderRecipientController;
use App\Http\Controllers\Agent\SupportTicketController;
use App\Http\Controllers\Agent\WithdrawController;

Route::prefix("agent")->name("agent.")->group(function(){
    Route::post("info",[GlobalController::class,'userInfo'])->name('get.user.info');
    Route::controller(DashboardController::class)->group(function(){
        Route::get('dashboard','index')->name('dashboard');
        Route::get('qr/scan/{qr_code}','qrScan')->name('qr.scan');
        Route::get('user/qr/scan/{qr_code}','userQrScan')->name('qr.scan.user');
        Route::post('logout','logout')->name('logout');
        Route::delete('delete/account','deleteAccount')->name('delete.account')->middleware('app.mode');
    });
    // profile
    Route::controller(ProfileController::class)->prefix("profile")->name("profile.")->middleware('app.mode')->group(function(){
        Route::get('/','index')->name('index');
        Route::put('password/update','passwordUpdate')->name('password.update');
        Route::put('update','update')->name('update');
    });

    //Receive Money
    Route::middleware('module:agent-receive-money')->group(function(){
        Route::controller(ReceiveMoneyController::class)->prefix('receive-money')->name('receive.money.')->group(function(){
            Route::get('/','index')->name('index');
        });
    });

    //Send Money
    Route::middleware('module:agent-transfer-money')->group(function(){
        Route::controller(SendMoneyController::class)->prefix('send-money')->name('send.money.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('confirmed','confirmedSendMoney')->name('confirmed')->middleware(['kyc.verification.guard']);
            Route::post('exist','checkUser')->name('check.exist');
        });
    });
    //Money In
    Route::middleware('module:agent-money-in')->group(function(){
        Route::controller(MoneyInController::class)->prefix('money-in')->name('money.in.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('confirmed','confirmedMoneyIn')->name('confirmed')->middleware(['kyc.verification.guard']);
            Route::post('exist','checkUser')->name('check.exist');
        });
    });
    //bill pay
    Route::middleware('module:agent-bill-pay')->group(function(){
        Route::controller(BillPayController::class)->prefix('bill-pay')->name('bill.pay.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('insert','billPayConfirmed')->name('confirm')->middleware(['kyc.verification.guard']);
        });
    });

    //Mobile TopUp
    Route::middleware('module:agent-mobile-top-up')->group(function(){
        Route::controller(MobileTopupController::class)->prefix('mobile-topup')->name('mobile.topup.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('type','selectType')->name('type');
             //manual methods
             Route::prefix('manual')->name('manual.')->group(function(){
                Route::get('/','manualTopUp')->name('index');
                Route::post('insert','topUpConfirmed')->name('confirm')->middleware('kyc.verification.guard');
            });
            //automatic method
            Route::prefix('automatic')->name('automatic.')->group(function(){
                Route::get('/','automaticTopUp')->name('index');
                Route::post('check-operator','checkOperator')->name('check.operator');
                Route::post('pay','payAutomatic')->name('pay')->middleware('kyc.verification.guard');
            });

        });
    });

    //withdraw money
    Route::middleware('module:agent-withdraw-money')->group(function(){
        Route::controller(WithdrawController::class)->prefix('withdraw')->name('money.out.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('insert','paymentInsert')->name('insert')->middleware(['kyc.verification.guard']);
            Route::get('preview','preview')->name('preview');
            Route::post('confirm','confirmMoneyOut')->name('confirm')->middleware(['kyc.verification.guard']);

            //check bank validation
            Route::post('check/flutterwave/bank','checkBanks')->name('check.flutterwave.bank');
            //automatic withdraw confirmed
            Route::post('automatic/confirmed','confirmMoneyOutAutomatic')->name('confirm.automatic')->middleware(['kyc.verification.guard']);

        });
    });


    //add money
    Route::middleware('module:agent-add-money')->group(function(){
        Route::controller(AddMoneyController::class)->prefix("add-money")->name("add.money.")->group(function(){
            Route::get('/','index')->name("index");
            Route::post('submit','submit')->name('submit')->middleware(['kyc.verification.guard']);
            Route::get('success/response/paypal/{gateway}','success')->name('payment.success');
            Route::get("cancel/response/paypal/{gateway}",'cancel')->name('payment.cancel');
            //manual gateway
            Route::get('manual/payment','manualPayment')->name('manual.payment');
            Route::post('manual/payment/confirmed','manualPaymentConfirmed')->name('manual.payment.confirmed');
            //flutterwave gateway
            Route::get('flutterwave/callback', 'flutterwaveCallback')->name('flutterwave.callback');

            //sslcommerz
            Route::post('sslcommerz/success','sllCommerzSuccess')->name('ssl.success')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);
            Route::post('sslcommerz/fail','sllCommerzFails')->name('ssl.fail')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);
            Route::post('sslcommerz/cancel','sllCommerzCancel')->name('ssl.cancel')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);
            //Stripe
            Route::get('stripe/payment/success/{trx}','stripePaymentSuccess')->name('stripe.payment.success');
            //coingate
            Route::match(['get','post'],'coingate/success/response/{gateway}','coinGateSuccess')->name('coingate.payment.success');
            Route::match(['get','post'],"coingate/cancel/response/{gateway}",'coinGateCancel')->name('coingate.payment.cancel');

            //crypto
            Route::prefix('payment')->name('payment.')->group(function() {
                Route::get('crypto/address/{trx_id}','cryptoPaymentAddress')->name('crypto.address');
                Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm');
            });
            //redirect submit payment
            Route::get('redirect/form/{gateway}', 'redirectUsingHTMLForm')->name('payment.redirect.form')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);
            //redirect with Btn Pay
            Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('payment.btn.pay')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);
            Route::post("callback/response/{gateway}",'callback')->name('payment.callback')->withoutMiddleware(['web','auth:agent','verification.guard.agent','agent.google.two.factor']);
            Route::get('success/response/{gateway}','successGlobal')->name('payment.global.success');
            Route::get("cancel/response/{gateway}",'cancelGlobal')->name('payment.global.cancel');

            // POST Route For Unauthenticated Request
            Route::post('success/response/{gateway}', 'postSuccess')->name('payment.global.success')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);
            Route::post('cancel/response/{gateway}', 'postCancel')->name('payment.global.cancel')->withoutMiddleware(['auth:agent','verification.guard.agent','agent.google.two.factor']);


        });
    });

    //Sender Recipient
    Route::controller(SenderRecipientController::class)->prefix('sender-recipient')->name('sender.recipient.')->group(function(){
        Route::get('/','index')->name('index');
        Route::get('/add','addRecipient')->name('add');
        Route::post('/add','storeRecipient');
        Route::get('edit/{id}','editRecipient')->name('edit');
        Route::put('update','updateRecipient')->name('update');
        Route::delete('delete','deleteRecipient')->name('delete');
        Route::post('find/user','checkUser')->name('check.user');
        Route::post('get/create-input','getTrxTypeInputs')->name('create.get.input');
        Route::post('get/edit-input','getTrxTypeInputsEdit')->name('edit.get.input');
        Route::get('send/remittance/{id}','sendRemittance')->name('send.remittance');
    });

     //Receiver Recipient
     Route::controller(ReceiverRecipientController::class)->prefix('receiver-recipient')->name('receiver.recipient.')->group(function(){
        Route::get('/','index')->name('index');
        Route::get('/add','addReceipient')->name('add');
        Route::post('/add','storeReceipient');
        Route::get('edit/{id}','editReceipient')->name('edit');
        Route::put('update','updateReceipient')->name('update');
        Route::delete('delete','deleteReceipient')->name('delete');
        Route::post('find/user','checkUser')->name('check.user');
        Route::post('get/create-input','getTrxTypeInputs')->name('create.get.input');
        Route::post('get/edit-input','getTrxTypeInputsEdit')->name('edit.get.input');
        Route::get('send/remittance/{id}','sendRemittance')->name('send.remittance');
    });


    //Remittance
    Route::middleware('module:agent-remittance-money')->group(function(){
        Route::controller(RemitanceController::class)->prefix('remittance')->name('remittance.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('get/token/sender','getTokenForSender')->name('get.token.sender');
            Route::post('get/token/receiver','getTokenForReceiver')->name('get.token.receiver');
            Route::post('confirmed','confirmed')->name('confirmed')->middleware(['kyc.verification.guard']);
            //for filters sender
            Route::post('get/recipient/country','getRecipientByCountry')->name('get.recipient.country');
            Route::post('get/recipient/transaction/type','getRecipientByTransType')->name('get.recipient.transtype');
            //for filters receiver
            Route::post('get/receiver/recipient/country','getRecipientByCountryReceiver')->name('get.receiver.recipient.country');
            Route::post('get/receiver/recipient/transaction/type','getRecipientByTransTypeReceiver')->name('get.receiver.recipient.transtype');
        });
    });

    //transactions
    Route::controller(TransactionController::class)->prefix("transactions")->name("transactions.")->group(function(){
        Route::get('/{slug?}','index')->name('index')->whereIn('slug',['add-money','withdraw','transfer-money','bill-pay','mobile-topup','remittance','money-out','money-in']);
        Route::post('search','search')->name('search');
    });
    //Profits
    Route::controller(ProfitsController::class)->prefix("profits")->name("profits.")->group(function(){
        Route::get('/','index')->name('index');
    });
    //google-2fa
    Route::controller(SecurityController::class)->prefix("security")->name('security.')->group(function(){
        Route::get('google/2fa','google2FA')->name('google.2fa');
        Route::post('google/2fa/status/update','google2FAStatusUpdate')->name('google.2fa.status.update')->middleware('app.mode');
    });
    //support tickets
    Route::controller(SupportTicketController::class)->prefix("support/ticket")->name("support.ticket.")->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('conversation/{encrypt_id}','conversation')->name('conversation');
        Route::post('message/send','messageSend')->name('messaage.send');
    });

});
Route::get('agent/pusher/beams-auth', function (Request $request) {
    if(Auth::check() == false) {
        return response(['Inconsistent request'], 401);
    }
    $userID = Auth::user()->id;

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
    $publisherUserId = make_user_id_for_pusher("agent", $userID);
    try{
        $beamsToken = $beamsClient->generateToken($publisherUserId);
    }catch(Exception $e) {
        return response(['Server Error. Failed to generate beams token.'], 500);
    }

    return response()->json($beamsToken);
})->name('agent.pusher.beams.auth');
