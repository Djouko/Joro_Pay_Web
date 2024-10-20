<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RequestMoneyTrxExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','USER TYPE','REQUESTED FROM','REQUESTED TO','REQUEST AMOUNT','RECEIVER AMOUNT','CHARGE','PAYABLE','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::REQUESTMONEY)->where('attribute',PaymentGatewayConst::SEND)->latest()->get()->map(function($item,$key){
            return [
                'id'    => $key + 1,
                'trx'   => $item->trx_id,
                'user_type'=> "USER",
                'requested_from'  => $item->creator->email,
                'requested_to'  => $item->details->receiver_email,
                'request_amount'  =>  get_amount($item->details->charges->request_amount,$item->details->charges->sender_currency,2),
                'receiver_amount'  =>get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,2),
                'charge_amount'  =>  get_amount($item->details->charges->total_charge,$item->details->charges->sender_currency,2),
                'payable_amount'  => get_amount($item->details->charges->payable,$item->details->charges->sender_currency,2),
                'status'  => __( $item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

