<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    public function google2FA() {
        $user = auth()->user();
        $message = __("Your account secure with google 2FA");
        if($user->two_factor_status == false) $message = __("To enable two factor authentication (powered by google) please visit your web dashboard. Click here")." : " . setRoute("merchant.authorize.google.2fa");
        return Response::success([__('Request response fetch successfully!')],[
            'status' => $user->two_factor_status,
            'message'   => $message,
        ],200);

    }
    public function verifyGoogle2Fa(Request $request) {
        $validator = Validator::make($request->all(),[
            'code'      => "required",
        ]);
        if($validator->fails()) return Response::error($validator->errors()->all(),[]);
        $validated = $validator->validate();
        $code = $validated['code'];
        $user = auth()->user();

        if(!$user->two_factor_secret) {
            return Response::error([__('Your secret key is not stored properly. Please contact with system administrator')],[],400);
        }
        if(google_2fa_verify_api($user->two_factor_secret,$code)) {
            $user->update([
                'two_factor_verified'   => true,
            ]);
            return Response::success([__('Google 2FA successfully verified!')],[],200);
        }else if(google_2fa_verify($user->two_factor_secret,$code) === false) {
            return Response::error([__('Invalid authentication code')],[],400);
        }
        return Response::error([__('Failed to login. Please try again')],[],500);
    }
}
