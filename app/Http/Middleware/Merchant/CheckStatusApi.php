<?php

namespace App\Http\Middleware\Merchant;

use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStatusApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if((Auth::user()->email_verified == 1 ) &&
         (Auth::user()->sms_verified == 1 ) &&
         (Auth::user()->status == 1)
         ){
            return $next($request);
        }else{
            if(Auth::user()->status == 0){
                $error = ['errors'=>[__('Account Is Deactivated')]];
                return Helpers::error($error);
            }elseif($user->email_verified == 0){;
                return merchantMailVerificationTemplateApi($user);
            }else if($user->sms_verified == 0){
                $error = ['errors'=>[__('Sms verification is required')]];
                return Helpers::error($error);
            }
        }
    }
}
