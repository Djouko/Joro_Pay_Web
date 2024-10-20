<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentQrCode extends Model
{
    use HasFactory;
    protected $table = "agent_qr_codes";
    protected $guarded = ['id'];
    protected $casts = [
        'agent_id' => 'integer',
        'qr_code' => 'string',
    ];
}
