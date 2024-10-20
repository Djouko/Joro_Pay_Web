<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\JsonResource;

class MakePaymentLogs extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $statusInfo = [
            "success" =>      1,
            "pending" =>      2,
            "rejected" =>     3,
        ];
        if($this->attribute == payment_gateway_const()::RECEIVED){
            return[
                'id' => @$this->id,
                'type' =>$this->attribute,
                'trx' => @$this->trx_id,
                'transaction_type' => $this->type,
                'transaction_heading' => "Received Money from @" .@$this->details->sender->fullname." (".@$this->details->sender->full_mobile.")",
                'recipient_received' => getAmount(@$this->request_amount,2).' '.get_default_currency_code(),
                'current_balance' => getAmount(@$this->available_balance,2).' '.get_default_currency_code(),
                'status' => @$this->stringStatus->value ,
                'date_time' => @$this->created_at ,
                'status_info' =>(object)@$statusInfo ,
            ];

        }
    }
}
