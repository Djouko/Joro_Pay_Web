<?php

namespace App\Http\Middleware\Merchant;

use App\Http\Helpers\Api\Helpers;
use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class ApiAuthenticator extends Authenticate
{
    /**
     * Determine if the user is authenticated and authorized to access the requested resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Validation\UnauthorizedException
     */
    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('merchant_api')->check()) {
            return $this->auth->shouldUse('merchant_api');
        }

        throw new UnauthorizedException('sorry');
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\UnauthorizedException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (UnauthorizedException $e) {
            $message = ['error'=>[__('Sorry, You are unauthorized merchant user')]];
            return Helpers::unauthorized($data = null, $message);
        }

        return $next($request);
    }

}
