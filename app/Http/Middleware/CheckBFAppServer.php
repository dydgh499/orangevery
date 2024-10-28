<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBFAppServer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        #if($request->ip() === '139.150.83.168') //TODO: 139.150.83.168는 sign-in, pay-modules 2가지 앤드포인트만 사용
        return $next($request);
    }
}
