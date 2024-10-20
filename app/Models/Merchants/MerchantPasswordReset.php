<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantPasswordReset extends Model
{
    use HasFactory;
    protected $guarded = [
        'id',
    ];
    public function merchant() {
        return $this->belongsTo(Merchant::class)->select('id','username','email','firstname','lastname');
    }
}
