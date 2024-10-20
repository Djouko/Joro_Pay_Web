<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSmsStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guard('web')->check()) {
            $user = auth()->user();
            if ($user->status == true && $user->sms_verified == true) {
                return $next($request);
            } else {
                return redirect()->route('user.authorize.sms');
            }
        }elseif(auth()->guard('merchant')->check()){
            $user = auth()->user();
            if ($user->status == true && $user->sms_verified == true) {
                return $next($request);
            } else {
                return redirect()->route('merchant.authorize.sms');
            }
        }elseif(auth()->guard('agent')->check()){
            $user = auth()->user();
            if ($user->status == true && $user->sms_verified == true) {
                return $next($request);
            } else {
                return redirect()->route('agent.authorize.sms');
            }
        }
        abort(403);
    }
}
