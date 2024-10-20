<?php

namespace App\Traits\Merchant;

use App\Models\Merchants\MerchantQrCode;

trait UserPartials{
	public function createQr(){
		$user = $this->user();
	    $qrCode = $user->qrCode()->first();
        $in['merchant_id'] = $user->id;;
        $in['qr_code'] =  $user->email;
	    if(!$qrCode){
            MerchantQrCode::create($in);
	    }else{
            $qrCode->fill($in)->save();
        }
	    return $qrCode;
	}

	protected function user(){
		return userGuard()['user'];
	}

}
