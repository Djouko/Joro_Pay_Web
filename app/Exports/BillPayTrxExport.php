<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BillPayTrxExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','TYPE','FULL NAME','USER TYPE','BILL TYPE NAME','BILL NUMBER','BILL AMOUNT','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::BILLPAY)->latest()->get()->map(function($item,$key){
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
                'type'  =>  $item->details->bill_type??"MANUAL",
                'full_name'  => $item->creator->fullname,
                'user_type'  => $user_type,
                'bill_type'  =>  @$item->details->bill_type_name,
                'bill_number'  =>  @$item->details->bill_number,
                'amount'  =>  get_amount($item->request_amount,get_default_currency_code(),4),
                'status'  => __( $item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

