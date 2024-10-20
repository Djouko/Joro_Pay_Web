<?php

namespace App\Models;

use App\Constants\GlobalConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Agent\UserPartials;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Agent extends Authenticatable
{
    use UserPartials,HasApiTokens, HasFactory, Notifiable;
    protected $appends = ['fullname','agentImage','stringStatus','lastLogin','kycStringStatus'];
    protected $dates = ['deleted_at'];
    protected $guarded = ["id"];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'firstname' => 'string',
        'lastname' => 'string',
        'username' => 'string',
        'store_name' => 'string',
        'email' => 'string',
        'mobile_code' => 'string',
        'mobile' => 'string',
        'full_mobile' => 'string',
        'password' => 'string',
        'refferal_user_id' => 'integer',
        'image' => 'string',
        'status' => 'integer',
        'email_verified_at' => 'datetime',
        'address'           => 'object',
        'email_verified'           => 'integer',
        'sms_verified'           => 'integer',
        'kyc_verified'           => 'integer',
        'ver_code'           => 'integer',
        'ver_code_send_at'           => 'datetime',
        'two_factor_verified'           => 'integer',
        "two_factor_status"           => "integer",
        "two_factor_secret"           => "string",
        'device_id'           => 'string',
        'remember_token'           => 'string',
        'deleted_at'           => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    public function scopeSmsUnverified($query)
    {
        return $query->where('sms_verified', false);
    }

    public function scopeEmailUnverified($query)
    {
        return $query->where('email_verified', false);
    }

    public function scopeEmailVerified($query) {
        return $query->where("email_verified",true);
    }

    public function scopeKycVerified($query) {
        return $query->where("kyc_verified",GlobalConst::VERIFIED);
    }

    public function scopeKycUnverified($query)
    {
        return $query->whereNot('kyc_verified',GlobalConst::VERIFIED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', false);
    }

    public function kyc()
    {
        return $this->hasOne(AgentKycData::class);
    }

    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function wallet()
    {
        return $this->hasOne(AgentWallet::class,'agent_id');
    }

    public function getAgentImageAttribute() {
        $image = $this->image;

        if($image == null) {
            return files_asset_path('profile-default');
        }else if(filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }else {
            return files_asset_path("agent-profile") . "/" . $image;
        }
    }

    public function passwordResets() {
        return $this->hasMany(UserPasswordReset::class,"user_id");
    }

    public function scopeGetSocial($query,$credentials) {
        return $query->where("email",$credentials);
    }

    public function getStringStatusAttribute() {
        $status = $this->status;
        $data = [
            'class' => "",
            'value' => "",
        ];
        if($status == GlobalConst::ACTIVE) {
            $data = [
                'class'     => "badge badge--success",
                'value'     => "active",
            ];
        }else if($status == GlobalConst::BANNED) {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "banned",
            ];
        }
        return (object) $data;
    }

    public function getKycStringStatusAttribute() {
        $status = $this->kyc_verified;
        $data = [
            'class' => "",
            'value' => "",
        ];
        if($status == GlobalConst::APPROVED) {
            $data = [
                'class'     => "badge badge--success",
                'value'     => "Verified",
            ];
        }else if($status == GlobalConst::PENDING) {
            $data = [
                'class'     => "badge badge--warning",
                'value'     => "Pending",
            ];
        }else if($status == GlobalConst::REJECTED) {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "Rejected",
            ];
        }else {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "Unverified",
            ];
        }
        return (object) $data;
    }

    public function loginLogs(){
        return $this->hasMany(AgentLoginLog::class);
    }

    public function getLastLoginAttribute() {
        if($this->loginLogs()->count() > 0) {
            return $this->loginLogs()->get()->last()->created_at->format("H:i A, d M Y");
        }

        return "N/A";
    }
    public function qrCode()
    {
        return $this->hasOne(AgentQrCode::class,'agent_id');
    }

    public function scopeSearch($query,$data) {
        return $query->where(function($q) use ($data) {
            $q->where("username","like","%".$data."%");
        })->orWhere("email","like","%".$data."%")->orWhere("full_mobile","like","%".$data."%");
    }
    public function scopeNotAuth($query) {
        $query->whereNot("id",auth()->user()->id);
    }
    public function modelGuardName() {
        return "agent";
    }

}
