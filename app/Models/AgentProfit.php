<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentProfit extends Model
{
    use HasFactory;

    protected $casts = [
        'agent_id' => 'integer',
        'transaction_id' => 'integer',
        'percent_charge' => 'double',
        'fixed_charge' => 'double',
        'total_charge' => 'double',
    ];

    public function transactions()
    {
        return $this->belongsTo(Transaction::class,'transaction_id');
    }
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    public function scopeAgentAuth($query) {
        $query->where("agent_id",auth()->user()->id);
    }
}
