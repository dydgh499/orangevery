<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Ablilty\Ablilty;

class CheckDevelopOfficeIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Ablilty::isDevOffice($request->ip()))
            return $next($request);
        else
            return response('Your IP has been temporarily blocked due to excessive requests. Access information will be analyzed.', 403);
    }
}
