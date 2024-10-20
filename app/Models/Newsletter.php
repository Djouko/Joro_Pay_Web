<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
class Newsletter extends Model
{
    use HasFactory,Notifiable;
    protected $table = "newsletters";
    protected $guarded = ['id'];
    protected $appends = [
        'editData',
    ];
    public function getEditDataAttribute() {

        $data = [
            'id'      => $this->id,
            'name'      => $this->name,
            'email'      => $this->email,
            'status'      => $this->status,
        ];

        return json_encode($data);
    }
    public function scopeSearch($query,$text) {
        $query->where(function($q) use ($text) {
            $q->where("email","like","%".$text."%");
        })->orWhere("name","like","%".$text."%");
    }
}
