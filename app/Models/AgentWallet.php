<?php

namespace App\Models;

use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentWallet extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['balance', 'status','agent_id','currency_id','created_at','updated_at'];
    protected $casts = [
        'agent_id' => 'integer',
        'currency_id' => 'integer',
        'balance' => 'double',
        'status' => 'integer',
    ];

    public function scopeAuth($query) {
        return $query->where('agent_id',auth()->user()->id);
    }

    public function scopeActive($query) {
        return $query->where("status",true);
    }


    public function agent() {
        return $this->belongsTo(Agent::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class,'currency_id');
    }


    public function scopeSender($query) {
        return $query->whereHas('currency',function($q) {
            $q->where("sender",GlobalConst::ACTIVE);
        });
    }
}
