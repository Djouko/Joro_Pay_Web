<?php

use App\Http\Controllers\PaymentGateway\QrPay\v1\DemoCheckoutController;
use Illuminate\Support\Facades\Route;


Route::controller(DemoCheckoutController::class)->prefix('merchant-checkout')->name('merchant.checkout.')->group(function(){
    Route::get('/','index')->name('index');
    Route::get('get-token','getToken')->name('get.token');
    Route::post('initiate-payment','initiatePayment')->name('initiate.payment');
    Route::get('success','paySuccess')->name('success');
    Route::get('cancel','payCancel')->name('cancel');
});
