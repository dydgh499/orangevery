<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Manager\Service\BrandInfo;
use Illuminate\Support\Facades\Redis;

class AuthAccountLock
{
    static public function getPasswordWrongCounter($user)
    {
        $key_name = 'password-wrong'.$user->brand_id.'-'.$user->level."-".$user->id;
        return (int)Redis::get($key_name);
    }

    static public function setPasswordWrongCounter($user)
    {
        $key_name = 'password-wrong'.$user->brand_id.'-'.$user->level."-".$user->id;
        $count = self::getPasswordWrongCounter($user) + 1;
        Redis::set($key_name, $count);

        $brand = BrandInfo::getBrandById($user->brand_id);   
        return $brand['pv_options']['free']['secure']['account_lock_limit'] - $count;
    }

    static public function initPasswordWrongCounter($user)
    {
        $key_name = 'password-wrong'.$user->brand_id.'-'.$user->level."-".$user->id;
        Redis::set($key_name, 0, 'EX', 1);
    }

    static public function setUserLock($orm, $id)
    {
        return $orm->where('id', $id)->update([
            'is_lock' => 1, 
            'locked_at' => date('Y-m-d H:i:s')
        ]);
    }

    static public function setUserUnlock($user)
    {
        $user->is_lock = 0;
        $user->save();

        if(isset($user->mcht_name))
            $user->level = 10;
        
        self::initPasswordWrongCounter($user);
    }
}
