<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminProfitLogs implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','USER','USER TYPE','TRANSACTION TYPE','PROFIT AMOUNT','TIME'],
        ];
    }

    public function array(): array
    {
        return TransactionCharge::with('transactions')
        ->whereHas('transactions', function ($query) {
            $query->whereNotIn('type', [PaymentGatewayConst::TYPEADDMONEY, PaymentGatewayConst::TYPEMONEYOUT,PaymentGatewayConst::TYPEADDSUBTRACTBALANCE]);
        })
        ->latest()->get()->map(function($item,$key){
            if($item->transactions->user_id != null){
                $user_type =  "USER"??"";
            }elseif($item->transactions->agent_id != null){
                $user_type =  "AGENT"??"";
            }elseif($item->transactions->merchant_id != null){
                $user_type =  "MERCHANT"??"";
            }
            return [
                'id'    => $key + 1,
                'trx'  => $item->transactions->trx_id,
                'user'  =>@$item->transactions->creator->fullname,
                'user_type'  =>$user_type,
                'transaction_type'  => $item->transactions->type,
                'profit_amount'  =>  get_amount($item->total_charge,get_default_currency_code(),4),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

