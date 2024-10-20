<?php

namespace App\Models\Merchants;

use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use App\Models\Merchants\Merchant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SandboxWallet extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
