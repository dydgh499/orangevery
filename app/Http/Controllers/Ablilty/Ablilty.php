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

    static function isMyOperator($request)
    {
        return self::isOperator($request) && $request->user()->id === $id;
    }
}
