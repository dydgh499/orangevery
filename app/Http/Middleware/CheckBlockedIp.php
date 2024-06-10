<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CheckBlockedIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $blocked = Redis::get("blocked:".$request->ip());

        if ($blocked && $ip !== '123.142.69.187') {
            critical("차단된 IP 접속");
            return response('Your IP has been temporarily blocked due to excessive requests. Access information will be analyzed.', 429);
        }

        return $next($request);
    }
}
