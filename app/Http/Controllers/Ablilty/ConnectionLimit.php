<?php

namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use Illuminate\Support\Facades\Redis;

class ConnectionLimit
{
    static private $base_key = 'connection-limit:';
    static private function get($id)
    {
        $key = self::$base_key.$id;
        return Redis::get($key) ?? 0;        
    }

    static private function set($id)
    {
        $key = self::$base_key.$id;
        Redis::incr($key);
        Redis::expire($key, 60); // 60초 후 만료
    }

    static public function validate($request)
    {
        $id = optional($request->user())->id ?: $request->ip();
        if(self::get($id) > 500)
        {
            AbnormalConnection::tryMecro();
            return false;
        }
        else
        {
            self::set($id);
            return true;
        }
    }
}
