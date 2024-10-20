<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VirtualCardTrxExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL','TRX ID', 'USER','TYPE','AMOUNT','CHARGE','CARD NUMBER','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::VIRTUALCARD)->latest()->get()->map(function($item,$key){
            $card_number = $item->details->card_info->card_pan?? $item->details->card_info->maskedPan ?? $item->details->card_info->card_number ?? "";
            return [
                'id'    => $key + 1,
                'trx'  => $item->trx_id,
                'user'  =>  $item->user->fullname,
                'type'  => $item->remark,
                'amount'  =>  get_amount($item->request_amount,get_default_currency_code()),
                'charge'  => get_amount($item->charge->total_charge,$item->user_wallet->currency->code),
                'card_number'  => $card_number,
                'status'  => __( $item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

