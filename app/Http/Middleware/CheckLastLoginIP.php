<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\IPInfo;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Traits\ExtendResponseTrait;

class CheckLastLoginIP
{
    use ExtendResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->last_login_ip === $request->ip() || Ablilty::isDevLogin($request))
            return $next($request);
        else
        {
            if(IPInfo::isMobile($request->ip()) === IPInfo::isMobile($request->user()->last_login_ip))
                return $next($request);
            else
            {
                if(Ablilty::isOperator($request))
                    AbnormalConnection::notSameLoginIP();

                return $this->response(955);    
            }
        }
    }
}
