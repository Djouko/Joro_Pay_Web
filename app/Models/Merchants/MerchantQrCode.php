<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantQrCode extends Model
{
    use HasFactory;
    protected $table = "merchant_qr_codes";
    protected $guarded = ['id'];
    protected $casts = [
        'merchant_id' => 'integer',
        'qr_code' => 'string',
    ];
}
