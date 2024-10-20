<?php

namespace App\Models;

use App\Constants\PaymentGatewayConst;
use App\Models\Merchants\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentLink extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'amountCalculation',
        'stringStatus',
        'linkType',
        'shareLink',
    ];

    protected $casts = [
        'user_id'     => 'integer',
        'merchant_id'     => 'integer',
        'currency_id' => 'integer',
        'type'        => 'string',
        'token'       => 'string',
        'title'       => 'string',
        'image'       => 'string',
        'details'     => 'string',
        'limit'       => 'integer',
        'min_amount'  => 'double',
        'max_amount'  => 'double',
        'price'       => 'double',
        'qty'         => 'integer',
        'status'      => 'integer',
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeAuth($query) {
        $query->where("user_id",auth()->user()->id);
    }

    public function merchant(){
        return $this->belongsTo(Merchant::class);
    }

    public function scopeMerchantAuth($query) {
        $query->where("merchant_id",auth()->user()->id);
    }

    public function creator() {
        if($this->user_id != null) {
            return $this->user();
        }else if($this->merchant_id != null) {
            return $this->merchant();
        }
    }

    public function getLinkTypeAttribute(){
        $type = $this->type;

        if($type == PaymentGatewayConst::LINK_TYPE_SUB){
            return 'Products or subscriptions';
        }else{
            return 'Customers choose what to pay';
        }
    }


    public function scopeStatus($query, $status){
        $query->where('status',$status);
    }


    public function getAmountCalculationAttribute(){
        $price = $this->price;
        $limit = $this->limit;
        $min_amount = $this->min_amount;
        $max_amount = $this->max_amount;
        $qty = $this->qty;
        $amount = 0;

        if(!empty($price)){
            $amount = get_amount($price) . ' ('.$qty.')';
        }else{
            if($limit == 1){
                $amount = get_amount($min_amount) . ' - ' . get_amount($max_amount);
            }else{
                $amount = 'Unlimited';
            }
        }

        return $amount.' '.$this->currency;
    }

    public function getAmountValueAttribute(){
        $price = $this->price;
        $qty = $this->qty;
        $amount = 0;

        if(!empty($price)){
            $amount = ($price*$qty);
        }else{
           $amount = 0;
        }

        return $amount;
    }

    public function getStringStatusAttribute() {
        $status = $this->status;
        $data = [
            'class' => "",
            'value' => "",
        ];
        if($status == PaymentGatewayConst::STATUSSUCCESS) {
            $data = [
                'class'     => "badge badge--success",
                'value'     => "active",
            ];
        }else if($status == PaymentGatewayConst::STATUSPENDING) {
            $data = [
                'class'     => "badge badge--warning",
                'value'     => "Closed",
            ];
        }
        return (object) $data;
    }


    public function getShareLinkAttribute(){
        $token = $this->token;
        return setRoute('payment-link.share', $token);
    }
}
