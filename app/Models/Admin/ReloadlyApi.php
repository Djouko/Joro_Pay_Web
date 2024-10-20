<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReloadlyApi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const PROVIDER_RELOADLY     = "RELOADLY";
    const GIFT_CARD             = "GIFT-CARD";
    const UTILITY_PAYMENT       = "UTILITY-PAYMENT";
    const MOBILE_TOPUP          = "MOBILE-TOPUP";
    const STATUS_ACTIVE         = 1;
    const ENV_SANDBOX           = "SANDBOX";
    const ENV_PRODUCTION        = "PRODUCTION";

    protected $casts = [
        'credentials'   => 'object',
    ];

    /**
     * Get reloadly api configuration
     */
    public function scopeReloadly($query)
    {
        return $query->where('provider', self::PROVIDER_RELOADLY);
    }
    public function scopeGiftCard($query)
    {
        return $query->where('type', self::GIFT_CARD);
    }
    public function scopeUtilityPayment($query)
    {
        return $query->where('type', self::UTILITY_PAYMENT);
    }
    public function scopeMobileTopUp($query)
    {
        return $query->where('type', self::MOBILE_TOPUP);
    }

    /**
     * Get active record
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
