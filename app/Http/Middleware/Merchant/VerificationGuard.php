<?php

namespace App\Http\Middleware\Merchant;

use Closure;
use Illuminate\Http\Request;

class VerificationGuard
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
        $merchant = auth()->user();

        if($merchant->email_verified == false) return mailVerificationTemplateMerchant($merchant);
        return $next($request);
    }
}
