<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryRestriction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id'              => 'integer',
        'slug'            => 'string',
        'user_type'       => 'string',
        'data'            => 'object',
        'status'          => 'integer',
    ];
}
