<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthExternalApiEnableIP;

class CheckExternalApiEnableIP
{
    use ExtendResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(AuthExternalApiEnableIP::valiate($request) || Ablilty::isDevLogin($request))
            return $next($request);
        else
            return $this->extendResponse(9999, '허용된 인증정보가 아닙니다.');
    }
}


