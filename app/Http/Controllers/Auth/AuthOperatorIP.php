<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Redis;
use App\Models\Service\OperatorIP;

class AuthOperatorIP
{
    static public function valiate($brand_id, $ip)
    {
        return true;
        /*
        $ips = self::getStore($brand_id);
        return in_array($ip, $ips);
        */
    }

    static public function setStore($brand_id, $ips)
    {
        $key_name = "operator-ip-".$brand_id;
        Redis::set($key_name, json_encode($ips));
    }

    static public function getStore($brand_id)
    {
        $key_name = "operator-ip-".$brand_id;
        $ips = Redis::get($key_name);
        if($ips)
            return json_decode($ips, true);
        else
        {
            $ips = OperatorIP::where('brand_id', $brand_id)->get()->pluck('enable_ip')->all();
            self::setStore($brand_id, $ips);
            return $ips;
        }
    }
}
