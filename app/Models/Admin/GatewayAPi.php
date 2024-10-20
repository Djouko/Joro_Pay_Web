<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayAPi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'admin_id' => 'integer',
        'payment_gateway_status' => 'boolean',
        'card_status' => 'boolean',
        'wallet_status' => 'boolean',
        'secret_key' => 'string',
        'public_key' => 'string',
    ];

}
