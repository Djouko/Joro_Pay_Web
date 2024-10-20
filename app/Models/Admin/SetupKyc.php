<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetupKyc extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'slug'    => "string",
        'user_type'    => "string",
        'status'    => "integer",
        'last_edit_by'    => "integer",
        'fields'    => "object",
    ];

    public function scopeUserKyc($query) {
        return $query->where("user_type","USER")->active();
    }
    public function scopeMerchantKyc($query) {
        return $query->where("user_type","MERCHANT")->active();
    }
    public function scopeAgentKyc($query) {
        return $query->where("user_type","AGENT")->active();
    }

    public function scopeActive($query) {
        $query->where("status",true);
    }
}
