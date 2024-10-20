<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayLinkTrxExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','TYPE','USER EMAIL','USER TYPE','PAYMENT TYPE','AMOUNT','PAYABLE','CONVERSION PAYABLE','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::TYPEPAYLINK)->latest()->get()->map(function($item,$key){
            if($item->user_id != null){
                $user_type =  "USER"??"";
            }elseif($item->agent_id != null){
                $user_type =  "AGENT"??"";
            }elseif($item->merchant_id != null){
                $user_type =  "MERCHANT"??"";
            }
            return [
                'id'    => $key + 1,
                'trx'  => $item->trx_id,
                'type'  => $item->type,
                'user_email'  => $item->creator->email,
                'user_type'  => $user_type,
                'payment_type'  => ucwords(str_replace('_',' ',$item->details->payment_type??__('Card Payment')) ),
                'amount'  => get_amount(@$item->request_amount, @$item->details->charge_calculation->sender_cur_code,4),
                'payable'  =>  get_amount(@$item->payable, @$item->details->charge_calculation->sender_cur_code,4),
                'conversion_payable'  =>  get_amount(@$item->details->charge_calculation->conversion_payable, @$item->details->charge_calculation->receiver_currency_code,4),
                'status'  => __( $item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

