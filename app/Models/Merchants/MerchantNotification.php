<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantNotification extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'message'   => 'object',
    ];

    protected $with = [
        'merchant',
    ];

    public function merchant() {
        return $this->belongsTo(Merchant::class);
    }

    public function scopeGetByType($query,$types) {
        if(is_array($types)) return $query->whereIn('type',$types);
    }

    public function scopeNotAuth($query) {
        $query->where("merchant_id","!=",auth()->user()->id);
    }

    public function scopeAuth($query) {
        $query->where("merchant_id",auth()->user()->id);
    }
}
