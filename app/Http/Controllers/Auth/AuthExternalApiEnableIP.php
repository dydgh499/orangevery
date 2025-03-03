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
        $json = self::get($request->user()->id);
        if(in_array($ip, $json['ips']) && $json['external_api'] === $external_api)
            return true;
        else
            return false;
    }

    static public function set($mcht, $ips)
    {
        $key_name = "external-enabled-ip-".$mcht->id;
        Redis::set($key_name, json_encode([
            'ips'           => $ips,
            'external_api'  => $mcht->api_key,
        ]), 'EX', 300);
    }

    static public function get($id)
    {
        $key_name = "external-enabled-ip-".$id;
        $ips = Redis::get($key_name);
        if($ips)
            return json_decode($ips, true);
        else
        {
            $ips = ExternalApiEnableIp::where('mcht_id', $id)
                    ->get()
                    ->pluck('enable_ip')
                    ->all();
            $mcht = Merchandise::where('id', $id)->first();
            if($mcht && count($ips))
            {
                self::set($mcht, $ips);
                return [
                    'ips'           => $ips,
                    'external_api'  => $mcht->api_key,
                ];    
            }
            else
            {
                return [
                    'ips'           => [],
                    'external_api'  => '',
                ];  
            }
        }
    }
}
