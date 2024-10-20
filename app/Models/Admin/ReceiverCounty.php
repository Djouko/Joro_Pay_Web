<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiverCounty extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'admin_id' => 'integer',
        'country' => 'string',
        'name' => 'string',
        'code' => 'string',
        'mobile_code' => 'string',
        'symbol' => 'string',
        'flag' => 'string',
        'rate' => 'double',
        'sender' => 'integer',
        'receiver' => 'integer',
        'status' => 'integer',
    ];
    protected $appends = [
        'editData',
        'countryImage'
    ];
    public function getReceiverCurrencyAttribute() {
        if($this->receiver == true) {
            return true;
        }
        return false;
    }
    public function getCountryImageAttribute() {
        $image = $this->flag;
        if($image == null) {
            return files_asset_path('default');
        }else if(filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }else {
            return files_asset_path("country-flag") . "/" . $image;
        }
    }
    public function getEditDataAttribute() {
        $role = "";
        if($this->sender == true && $this->receiver == false) {
            $role = "sender";
        }else if($this->receiver == true && $this->sender == false) {
            $role = "receiver";
        }
        $data = [
            'id'      => $this->id,
            'name'      => $this->name,
            'code'      => $this->code,
            'mobile_code'      => $this->mobile_code,
            'flag'      => $this->flag,
            'role'      => $role,
            'symbol'    => $this->symbol,
            'rate'      => get_amount($this->rate),
            'country'   => $this->country,
        ];

        return json_encode($data);
    }
    public function scopeSearch($query,$text) {
        $query->where(function($q) use ($text) {
            $q->where("country","like","%".$text."%");
        })->orWhere("name","like","%".$text."%")->orWhere("code","like","%".$text."%");
    }
    public function scopeActive($query) {
        return $query->where("status",true);
    }
    public function scopeBanned($query) {
        return $query->where("status",false);
    }

}
