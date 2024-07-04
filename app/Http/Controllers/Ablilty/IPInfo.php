<?php

namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class IPInfo
{
    static public function isMobile($ip)
    {
        $isBand = function($ip, $bands) {
            foreach($bands as $skt)
            {
                if(strlen($ip) > strlen($skt))
                {
                    $band = substr($ip, 0, strlen($skt));
                    if($band === $skt)
                        return true;
                }
            }
            return false;
        };
        $skt_band = [
            '115.161', '122.202', '223.38', '223.32', '122.32',
            '211.234', '121.190', '223.39', '223.33', '223.62',
            '203.226', '175.202', '223.57',   
        ];
        $kt_band = [
            '175.223', '175.252', '210.125', '211.246', '110.70',  
            '39.7', '118.235',  
        ];
        $lgu_band = [
            '114.200', '117.111', '211.36', '106.102', '61.43',  
            '125.188', '211.234', '106.101',
        ];
        if($isBand($ip, $skt_band))
            return 'SKT 이동통신';
        else if($isBand($ip, $kt_band))
            return 'KT 이동통신';
        else if($isBand($ip, $lgu_band))
            return 'LGU 이동통신';
        else
            return '';
    }

    static private function set($info)
    {
        Redis::set(request()->ip(), json_encode($info), 'EX', 86400);
    }

    static public function get($request)
    {
        if($request->ip() === '127.0.0.1' || in_array($request->ip(), json_decode(env('EXCEPT_IPS', "[]"), true)))
            return ["ip" => "127.0.0.1", "bogon" => true, 'country'=>'KR'];

        $info = Redis::get($request->ip());
        if($info)
            return json_decode($info, true);
        else
        {
            $token = env('IPINFO_API_KEY', '2c693805e1bced');
            $res = get("https://ipinfo.io/".$request->ip()."?token=$token", [], [
                'Authorization' => "Bearer $token",
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                "Accept" =>  "application/json",
            ]);
            
            if($res['code'] !== 200)
            {
                error(array_merge($request->all(), $res), 'ip blacklist API count over');
                return null;
            }
            else
            {
                self::set($res['body']);
                return $res['body'];
            }
        }
    }

    static public function validate($request)
    {
        if(Ablilty::isDevOffice($request) || $request->ip() === '127.0.0.1')
            return true;
        else
        {
            $info = self::get($request);
            if($info)
            {
                $regions = ['Da Nang', 'Hanoi', "Long An Povince", "Ho Chi Minh", 'Quảng Ninh', 'Bangkok'];
                if(strtoupper($info['country']) === 'KR')
                    return true;
                else if(in_array(strtoupper($info['country']), ['VN', 'TH']) && in_array($info['region'], $regions))
                    return true;
                else
                {   // 해외 IP 접속
                    AbnormalConnection::tryOverseasIP();
                    return false;
                }
            }
            else
                return true;    // 'ip blacklist API count over
        }
    }

    static public function setBlock($ip, $sec)
    {
        if(env('APP_ENV', 'production') === 'local')
            Redis::set('blocked:'.$ip, 1, 'EX', 1);
        else
            Redis::set('blocked:'.$ip, 1, 'EX', $sec);
    }

    static public function getBlock($ip)
    {
        return Redis::get("blocked:".$ip);
    }
}
