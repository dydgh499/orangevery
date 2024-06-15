<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Ablilty\Ablilty;

class CheckEditAbleTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Ablilty::isEditAbleTime())
            return $next($request);
        else
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
    }
}
