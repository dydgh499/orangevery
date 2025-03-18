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

    public function isValidUserAgent($request)
    {
        // 정상적인 브라우저의 User-Agent 패턴 목록
        $vaild_user_agents = [
            'Dalvik/2.1.0',
            'Mozilla/5.0', // 일반적인 브라우저 패턴 (Firefox, Chrome, Safari 등)
            'Opera',       // Opera 브라우저
            'MSIE',        // Internet Explorer
            'Trident',     // Internet Explorer 11+
            'facebookexternalhit',  // kakaotalk scrap
            'NetworkingExtension',
            'Darwin',
            'Microsoft Office',
            'NateOn',
            'ms-office',
            'Microsoft Windows Network Diagnostics',
            'GoogleAssociationService',
            'WebInfoUtil/1.0',
        ];
        $user_agent = $request->header('User-Agent');
        // User-Agent가 정상적인 브라우저 목록에 포함되는지 검사
        foreach ($vaild_user_agents as $validuser_agent) 
        {
            if (strpos($user_agent, $validuser_agent) !== false) 
                return true;
        }
        return false;
    }

    public function isExceptIP($request)
    {
        $except_ips = [
            '211.188.197.19'    //SKT 문자관련 IP 대역
        ];
        foreach ($except_ips as $except_ip) 
        {
            if (strpos($request->ip(), $except_ip) !== false) 
                return true;
        }
        return false;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if($this->isValidUserAgent($request) === false && $this->isExceptIP($request) === false)
            return $this->response(958);
        else
            return $next($request);
    }
}
