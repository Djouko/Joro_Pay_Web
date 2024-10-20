<?php

namespace App\Models;

use App\Models\Admin\ReceiverCounty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipient extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'user_id' => 'integer',
        'country' => 'integer',
        'type' => 'string',
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
        $query->where("user_id",auth()->user()->id);
    }
    public function getFullnameAttribute()
    {

        return $this->firstname . ' ' . $this->lastname;
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function receiver_country() {
        return $this->belongsTo(ReceiverCounty::class,'country');
    }


}
