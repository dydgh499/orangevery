<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Ablilty\Ablilty;

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
            return $next($request);
        else
            return $this->response(951);
    }
}
