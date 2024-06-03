<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Response as Res;

class ForeginIPBlockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = "2c693805e1bced";
        $ip = $request->ip() === '127.0.0.1' ? '183.107.112.147' : $request->ip();
        // 
        $res = get("https://ipinfo.io/$ip/geo", []);
        if($res['code'] !== 200)
        {
            error(array_merge($request->all(), $res), 'ip blacklist API count over');
            return $next($request);
        }
        else
        {
            if(strtoupper($res['body']['country']) === 'KR')
                return $next($request);
            else
            {
                $msg = '이상접근이 탐지되었습니다. 해당 접속로그는 관리자에게 전송되어 분석될 예정입니다.';
                error(array_merge($request->all(), $res), $msg);
                
                return Response::json(['message'=>$msg, 'data'=>$request->all()], 403, [], JSON_UNESCAPED_UNICODE);   
            }
        }
    }
}
