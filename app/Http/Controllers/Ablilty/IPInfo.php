<?php

namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\Ablilty;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class IPInfo
{
    static public function set($info)
    {
        Redis::set(request()->ip(), json_encode($info), 'EX', 1800);
    }

    static public function get($request)
    {
        $info = Redis::get($request->ip());
        if($info)
            return json_decode($info, true);
        else
        {
            $token = env('IPINFO_API_KEY', '2c693805e1bced');
            $res = get("https://ipinfo.io/".$request->ip(), [], [
                'Authorization' => "Bearer {$token}",
                'User-Agent' => 'Laravel',
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
                if(strtoupper($info['country']) === 'KR')
                    return true;
                else if(strtoupper($info['country']) === 'VN' && in_array($info['region'], ['Da Nang', 'Hanoi']))
                    return true;
                else
                {   // 해외 IP 접속
                    critical('해외 IP 접속');
                    return false;
                }
            }
            else
                return true;    // 'ip blacklist API count over
        }
    }
}
