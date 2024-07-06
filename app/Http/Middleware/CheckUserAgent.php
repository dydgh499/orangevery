<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Traits\ExtendResponseTrait;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Ablilty\AbnormalConnection;

class CheckUserAgent
{
    use ExtendResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 정상적인 브라우저의 User-Agent 패턴 목록
        $vaild_user_agents = [
            'Mozilla/5.0', // 일반적인 브라우저 패턴 (Firefox, Chrome, Safari 등)
            'Opera',       // Opera 브라우저
            'MSIE',        // Internet Explorer
            'Trident',     // Internet Explorer 11+
            'facebookexternalhit',  // kakaotalk scrap
            'NetworkingExtension',
        ];
        $user_agent = $request->header('User-Agent');
        // User-Agent가 정상적인 브라우저 목록에 포함되는지 검사
        $is_valid = false;
        foreach ($vaild_user_agents as $validuser_agent) 
        {
            if (strpos($user_agent, $validuser_agent) !== false) {
                $is_valid = true;
                break;
            }
        }

        if (!$is_valid)
        {
            critical('user-agent 없음 ('.request()->ip().")", request()->headers->all());
            AbnormalConnection::notBrowser();
            return $this->response(951);
        }

        return $next($request);
    }
}
