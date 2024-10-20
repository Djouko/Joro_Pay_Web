<?php

namespace App\Models\Merchants;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOrderRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id','trx_id','token','access_token'];

    protected $casts = [
        'data'  => 'object',
    ];

    public function merchant() {
        return $this->belongsTo(Merchant::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
