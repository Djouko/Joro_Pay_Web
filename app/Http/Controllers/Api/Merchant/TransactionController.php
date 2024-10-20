<?php

namespace App\Http\Controllers\Api\Merchant;


use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Api\Helpers;
use App\Http\Resources\Merchant\AddSubBalanceLogs;
use App\Http\Resources\Merchant\MakePaymentLogs;
use App\Http\Resources\Merchant\MerchantPaymentLogs;
use App\Http\Resources\Merchant\MoneyOutLogs;
use App\Http\Resources\Merchant\PayLinkResource;

class TransactionController extends Controller
{
    public function slugValue($slug) {
        $values =  [
            'money-out'             => PaymentGatewayConst::TYPEMONEYOUT,
            'merchant-payment'      => PaymentGatewayConst::MERCHANTPAYMENT,
            'received-payment'      => PaymentGatewayConst::TYPEMAKEPAYMENT,
            'add-sub-balance'       => PaymentGatewayConst::TYPEADDSUBTRACTBALANCE,
        ];

        if(!array_key_exists($slug,$values)) return abort(404);
        return $values[$slug];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug = null) {
        $moneyOut           = Transaction::merchantAuth()->moneyOut()->orderByDesc("id")->get();
        $merchant_payment   = Transaction::merchantAuth()->merchantPayment()->orderByDesc("id")->get();
        $make_payment       = Transaction::merchantAuth()->makePayment()->orderByDesc("id")->get();
        $addSubBalance      = Transaction::merchantAuth()->addSubBalance()->orderByDesc("id")->get();
        $payLink            = Transaction::merchantAuth()->payLink()->orderByDesc('id')->get();

        $transactions = [
            'money_out'         => MoneyOutLogs::collection($moneyOut),
            'merchant_payment'  => MerchantPaymentLogs::collection($merchant_payment),
            'make_payment'      => MakePaymentLogs::collection($make_payment),
            'add_sub_balance'   => AddSubBalanceLogs::collection($addSubBalance),
            'pay_link'          => PayLinkResource::collection($payLink),
        ];
        $transactions = (object)$transactions;

        $transaction_types = [
            'money_out'             => PaymentGatewayConst::TYPEMONEYOUT,
            'merchant_payment'      => PaymentGatewayConst::MERCHANTPAYMENT,
            'received_payment'      => PaymentGatewayConst::TYPEMAKEPAYMENT,
            'add_sub_balance'       => PaymentGatewayConst::TYPEADDSUBTRACTBALANCE,
            'pay_link'              => PaymentGatewayConst::TYPEPAYLINK,

        ];
        $transaction_types = (object)$transaction_types;
        $data =[
            'transaction_types' => $transaction_types,
            'transactions'=> $transactions,
        ];
        $message =  ['success'=>[__('All Transactions')]];
        return Helpers::success($data,$message);

    }
}
