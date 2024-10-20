<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MoneyInLogs extends JsonResource
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
        return[
            'id' => @$this->id,
            'type' =>$this->attribute,
            'trx' => @$this->trx_id,
            'transaction_type' => $this->type,
            'transaction_heading' => "Money In From @" . @$this->details->sender_email,
            'request_amount' => getAmount(@$this->request_amount,2).' '.$this->details->charges->sender_currency,
            'total_charge' => getAmount(0,2).' '.$this->details->charges->sender_currency,
            'payable' => getAmount(@$this->payable,2).' '.$this->details->charges->sender_currency,
            'recipient_received' => getAmount(@$this->details->charges->receiver_amount,2).' '.$this->details->charges->receiver_currency,
            'current_balance' => getAmount(@$this->available_balance,2).' '.get_default_currency_code(),
            'status' => @$this->stringStatus->value ,
            'date_time' => @$this->created_at ,
            'status_info' =>(object)@$statusInfo ,
        ];
    }
}
