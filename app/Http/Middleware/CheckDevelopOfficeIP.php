<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDevelopOfficeIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if(in_array($ip, ["183.107.112.147", "121.183.143.103", "125.179.103.82", '127.0.0.1']))
            return $next($request);
        else
            return response('Your IP has been temporarily blocked due to excessive requests. Access information will be analyzed.', 403);
    }
}
