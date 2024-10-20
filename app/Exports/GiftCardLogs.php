<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GiftCardLogs implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','CARD NAME','CARD IMAGE','RECEIVER EMAIL','RECEIVER PHONE','PAYABLE UNIT PRICE','TOTAL CHARGE','PAYABLE AMOUNT','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::GIFTCARD)->where('attribute',PaymentGatewayConst::SEND)->latest()->get()->map(function($item,$key){
            return [
                'id'                => $key + 1,
                'trx'               => $item->trx_id,
                'card_name'         => $item->details->card_info->card_name,
                'card_image'        => $item->details->card_info->card_image,
                'receiver_email'    =>$item->details->card_info->recipient_email,
                'receiver_phone'    =>$item->details->card_info->recipient_phone,
                'payable_unit_price'=>  get_amount($item->details->charge_info->sender_unit_price,$item->details->charge_info->wallet_currency),
                'total_charge'      =>  get_amount($item->charge->total_charge,$item->details->charge_info->wallet_currency),
                'payable_amount'    => get_amount($item->payable,$item->details->charge_info->wallet_currency),
                'status'            => __( $item->stringStatus->value),
                'time'              =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

