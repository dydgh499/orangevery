<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Redis;
use App\Models\Service\OperatorIP;

class AuthOperatorIP
{
    static public function valiate($brand_id, $ip)
    {
        $ips = self::get($brand_id);
        return in_array($ip, $ips);
    }

    static public function set($brand_id, $ips)
    {
        $key_name = "operator-ip-".$brand_id;
        Redis::set($key_name, json_encode($ips), 'EX', 300);
    }

    static public function get($brand_id)
    {
        $key_name = "operator-ip-".$brand_id;
        $ips = Redis::get($key_name);
        if($ips)
            return json_decode($ips, true);
        else
        {
            $ips = OperatorIP::where('brand_id', $brand_id)->get()->pluck('enable_ip')->all();
            self::set($brand_id, $ips);
            return $ips;
        }
    }
}
