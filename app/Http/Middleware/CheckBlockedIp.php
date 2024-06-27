<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\IPInfo;

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
        if (IPInfo::getBlock($ip)) 
        {
            AbnormalConnection::tryBlockIP();
            return response('Your IP has been temporarily blocked due to excessive requests. Access information will be analyzed.', 429);
        }
        return $next($request);
    }
}
