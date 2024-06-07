<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Redis;

class AccountLock
{
    static public $RETRY_MAX_COUNT = 3;

    static function setPasswordWrongCounter($user)
    {
        $key_name = 'password-wrong'.$user->brand_id.'-'.$user->level."-".$user->id;
        $count = ((int)Redis::get($key_name)) + 1;
        Redis::set($key_name, $count);
        return self::$RETRY_MAX_COUNT - $count;
    }

    static function initPasswordWrongCounter($user)
    {
        $key_name = 'password-wrong'.$user->brand_id.'-'.$user->level."-".$user->id;
        Redis::set($key_name, 0, 'EX', 1);
    }

    static function setUserLock($orm, $id, $lock_status)
    {
        return $orm->where('id', $id)->update([
            'is_lock' => $lock_status, 
            'locked_at' => date('Y-m-d H:i:s')
        ]);
    }
}
