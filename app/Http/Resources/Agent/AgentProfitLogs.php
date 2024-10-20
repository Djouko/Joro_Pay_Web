<?php

namespace App\Http\Resources\Agent;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentProfitLogs extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'trx' => $this->transactions->trx_id,
            'transaction_type' => $this->transactions->type,
            'transaction_amount' => getAmount($this->transactions->details->charges->sender_amount,2).' '.get_default_currency_code(),
            'profit_amount' => getAmount($this->total_charge,2).' '.get_default_currency_code(),
            'created_at' => $this->created_at,
        ];
    }
}
