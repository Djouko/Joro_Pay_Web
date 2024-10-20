<?php

namespace App\Http\Resources\Merchant;

use App\Constants\PaymentGatewayConst;
use Illuminate\Http\Resources\Json\JsonResource;

class PayLinkResource extends JsonResource
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
            'id'                    => $this->id,
            'trx'                   => $this->trx_id,
            'title'                 => __('Add Balance via').' ('.__("pay Link").')',
            'transaction_type'      => $this->type,
            'request_amount'        => get_amount($this->request_amount, @$this->details->charge_calculation->sender_cur_code),
            'payable'               => get_amount($this->details->charge_calculation->conversion_payable,  @$this->details->charge_calculation->receiver_currency_code),
            'exchange_rate'         => '1 ' .@$this->details->charge_calculation->receiver_currency_code.' = '.get_amount(@$this->details->charge_calculation->exchange_rate, @$this->details->charge_calculation->sender_cur_code),
            'total_charge'          => get_amount(@$this->details->charge_calculation->conversion_charge ?? 0,@$this->details->charge_calculation->sender_cur_code,4),
            'current_balance'       => get_amount($this->available_balance, @$this->details->charge_calculation->receiver_currency_code),
            'payment_type'          => $this->details->payment_type ??PaymentGatewayConst::TYPE_CARD_PAYMENT,
            'payment_type_gateway_data'     => [
                'payment_gateway' => $this->details->currency->name??""
            ],
            'payment_type_card_data'     => [
                'sender_email' => $this->details->email??"",
                'card_holder_name' => $this->details->card_name??"",
                'sender_card_last4' => $this->details->last4_card??"",
            ],
            'payment_type_wallet_data'     => [
                'sender_email' => $this->details->sender->email??""
            ],
            'status_value'          => $this->status,
            'status'                => $this->stringStatus->value,
            'date_time'             => $this->created_at,
            'status_info'           => (object)$statusInfo
        ];
    }
}
