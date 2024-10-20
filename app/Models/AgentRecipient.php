<?php

namespace App\Models;

use App\Constants\GlobalConst;
use App\Models\Admin\ReceiverCounty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentRecipient extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'agent_id' => 'integer',
        'country' => 'integer',
        'type' => 'string',
        'recipient_type' => 'string',
        'firstname' => 'string',
        'lastname' => 'string',
        'mobile_code' => 'string',
        'mobile' => 'string',
        'account_number' => 'string',
        'city' => 'string',
        'state' => 'string',
        'address' => 'string',
        'zip_code' => 'string',
        'details' => 'object',
    ];
    public function scopeAuth($query) {
        $query->where("agent_id",auth()->user()->id);
    }
    public function getFullnameAttribute()
    {

        return $this->firstname . ' ' . $this->lastname;
    }
    public function agent() {
        return $this->belongsTo(Agent::class);
    }
    public function receiver_country() {
        return $this->belongsTo(ReceiverCounty::class,'country');
    }

    public function scopeSender($query) {
        return $query->where("recipient_type",GlobalConst::SENDER);
    }
    public function scopeReceiver($query) {
        return $query->where("recipient_type",GlobalConst::RECEIVER);
    }
}
