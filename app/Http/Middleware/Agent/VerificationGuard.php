<?php

namespace App\Http\Middleware\Agent;

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
        $agent = auth()->user();
        if($agent->email_verified == false) return mailVerificationTemplateAgent($agent);
        return $next($request);
    }
}
