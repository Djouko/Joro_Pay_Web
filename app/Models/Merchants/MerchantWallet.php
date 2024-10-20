<?php

namespace App\Models\Merchants;

use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantWallet extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['balance', 'status','merchant_id','currency_id','created_at','updated_at'];
    protected $casts = [
        'merchant_id' => 'integer',
        'currency_id' => 'integer',
        'balance' => 'double',
        'status' => 'integer',
    ];

    public function scopeAuth($query) {
        return $query->where('merchant_id',auth()->user()->id);
    }

    public function scopeActive($query) {
        return $query->where("status",true);
    }


    public function merchant() {
        return $this->belongsTo(Merchant::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }


    public function scopeSender($query) {
        return $query->whereHas('currency',function($q) {
            $q->where("sender",GlobalConst::ACTIVE);
        });
    }
}
