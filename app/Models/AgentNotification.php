<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentNotification extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'message'   => 'object',
    ];

    protected $with = [
        'agent',
    ];

    public function agent() {
        return $this->belongsTo(Agent::class);
    }

    public function scopeGetByType($query,$types) {
        if(is_array($types)) return $query->whereIn('type',$types);
    }

    public function scopeNotAuth($query) {
        $query->where("agent_id","!=",auth()->user()->id);
    }

    public function scopeAuth($query) {
        $query->where("agent_id",auth()->user()->id);
    }
}
