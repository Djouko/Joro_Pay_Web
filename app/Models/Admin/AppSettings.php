<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'version' => 'string',
        'agent_version' => 'string',
        'merchant_version' => 'string',
        'splash_screen_image' => 'string',
        'agent_splash_screen_image' => 'string',
        'merchant_splash_screen_image' => 'string',
        'url_title' => 'string',
        'android_url' => 'string',
        'iso_url' => 'string',
    ];

}
