<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Auth\AuthOperatorIP;

class CheckOperator
{
    use ExtendResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Ablilty::isOperator($request))
        {
            if(AuthOperatorIP::valiate($request->user()->brand_id, $request->ip()) || Ablilty::isDevLogin($request))
                return $next($request);
            else
            {
                AbnormalConnection::tryOperationNotPermitted();
                return $this->response(951);
            }
        }
        else
        {
            AbnormalConnection::tryOperationNotPermitted();
            return $this->response(951);
        }
    }
}
