<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Redis;
use App\Models\Merchandise;
use App\Models\Merchandise\ExternalApiEnableIp;

class AuthExternalApiEnableIP
{
    static public function valiate($request)
    {
        $ip             = $request->ip();
        $external_api   = str_replace("Bearer ", "", $request->header('External-Api'));
        $json = self::get($request->user());
        if(in_array($ip, $json['ips']) && $json['api_key'] === $external_api)
            return true;
        else
            return false;
    }

    static public function set($mcht, $ips)
    {
        $key_name = "external-enabled-ip-".$mcht->id;
        Redis::set($key_name, json_encode([
            'ips'       => $ips,
            'api_key'  => $mcht->api_key,
        ]), 'EX', 300);
    }

    static public function get($mcht)
    {
        $key_name = "external-enabled-ip-".$mcht->id;
        $ips = Redis::get($key_name);
        if($ips)
            return json_decode($ips, true);
        else
        {
            $ips = ExternalApiEnableIp::where('mcht_id', $mcht->id)
                    ->pluck('enable_ip')
                    ->all();
            if($mcht && count($ips))
            {
                self::set($mcht, $ips);
                return [
                    'ips'       => $ips,
                    'api_key'   => $mcht->api_key,
                ];    
            }
            else
            {
                return [
                    'ips'       => [],
                    'api_key'   => '',
                ];  
            }
        }
    }
}
