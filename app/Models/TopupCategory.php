<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupCategory extends Model
{
    use HasFactory;
    protected $table = "topup_categories";
    protected $guarded = ['id'];
     protected $casts = [
        'admin_id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'status' => 'integer',
    ];
    protected $appends = [
        'editData',
    ];
    public function getEditDataAttribute() {

        $data = [
            'id'      => $this->id,
            'name'      => $this->name,
            'slug'      => $this->slug,
            'status'      => $this->status,
        ];

        return json_encode($data);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', false);
    }

    public function scopeSearch($query,$text) {
        $query->Where("name","like","%".$text."%");
    }
}
