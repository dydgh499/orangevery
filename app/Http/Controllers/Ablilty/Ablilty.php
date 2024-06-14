<?php
namespace App\Http\Controllers\Ablilty;

use Illuminate\Support\Facades\Redis;

class Ablilty
{
    static function isMyMerchandise($request, int $id)
    {
        return self::isMerchandise($request) && $request->user()->id === (int)$id;
    }
    
    static function isMerchandise($request)
    {
        return $request->user()->tokenCan(13) == false ? true : false;
    }

    static function isMySalesforce($request, int $id)
    {
        return self::isSalesforce($request) && $request->user()->id === $id;
    }

    static function isSalesforce($request)
    {
        $cond_1 = $request->user()->tokenCan(13) == true;
        $cond_2 = $request->user()->tokenCan(35) == false;
        return $cond_1 && $cond_2;
    }

    static function isOperator($request)
    {
        return $request->user()->tokenCan(35);
    }

    static function isMyOperator($request, int $id)
    {
        return self::isOperator($request) && $request->user()->id === $id;
    }

    static function isDevLogin($request)
    {
        return $request->user()->tokenCan(50) && self::isDevOffice($request);
    }

    static function isDevOffice($request)
    {
        $master_ips = ["183.107.112.147", "121.183.143.103", "125.179.103.82"];
        if(env('APP_ENV') === 'local')
            array_push($master_ips, '127.0.0.1');
        return in_array($request->ip(), $master_ips);
    }

    static function isBrandCheck($request, $brand_id, $is_dev_ok=false)
    {
        if($is_dev_ok)
        {
            $cond_1 = self::isDevLogin($request);
            $cond_2 = ($request->user()->brand_id !== $brand_id);
            if($cond_1 === false && $cond_2)
                return false;
            else
                return true;
        }
        else
        {
            if($request->user()->brand_id !== $brand_id)
                return false;
            else
                return true;
        }
    }
}
