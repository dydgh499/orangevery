<?php
namespace App\Http\Controllers\Auth;

use App\Models\Salesforce;
use App\Models\Merchandise;

use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Auth\AuthAccountLock;
use App\Http\Traits\Models\EncryptDataTrait;
use App\Enums\AuthLoginCode;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthPasswordChange
{
    use EncryptDataTrait;

    static public function getPasswordResetToken($user)
    {
        $inst = new AuthPasswordChange();
        $token = json_encode([
            'brand_id'  => $user->brand_id,
            'user_name' => $user->user_name,
            'level'     => $user->level,
        ]);
        return ['token' => $inst->aes256_encode($token), 'level' => $user->level];
    }

    static public function getTokenContent($token)
    {
        $result = [
            'result' => AuthLoginCode::WRONG_ACCESS->value, 
            'data' => [], 
            'msg' => '잘못된 접근입니다.',
        ];

        $inst = new AuthPasswordChange();
        $token = $inst->aes256_decode($token);
        if($token)
        {
            $user = json_decode($token, true);
            if($user && $user['user_name'] && $user['level'] && $user['brand_id'])
            {
                $result['result'] = AuthLoginCode::SUCCESS->value;
                $result['data'] = $user;
            }
        }
        return $result;
    }

    static public function updateFirstPassword($result, $user_pw)
    {
        $orm = $result['data']['level'] === 10 ? new Merchandise : new Salesforce;
        $user = $orm
            ->where('brand_id', $result['data']['brand_id'])
            ->where('user_name', $result['data']['user_name'])
            ->where('is_delete', false)
            ->first();

        if($user)
        {
            if(self::HashCheck($user, $user_pw))
            {
                $result['result'] = AuthLoginCode::NOT_ALLOW_FIRST_PASSWORD->value;
                $result['msg']    = '초기 패스워드로 업데이트할 수 없습니다.';
            }
            else
            {
                $user->user_pw = Hash::make($user_pw.$user->created_at);
                $user->password_change_at = date('Y-m-d H:i:s');
                $user->save();
                AuthAccountLock::initPasswordWrongCounter($user);
                $result['data'] = $user->loginInfo($result['data']['level']);
            }
        }
        else
        {
            $result['result'] = AuthLoginCode::WRONG_ACCESS->value;
            $result['msg'] = '잘못된 접근입니다.';
        }
        return $result;
    }


    static public function HashCheck($user, $_user_pw)
    {
        $created_at         = Carbon::parse($user->created_at);
        $password_change_at = Carbon::parse($user->password_change_at);
        $reference_time     = Carbon::parse('2024-06-15 17:20:00');

        if($created_at->greaterThanOrEqualTo($reference_time) || $password_change_at->greaterThanOrEqualTo($reference_time)) 
        {   //06/15 16시 업데이트 이후 수정된 건들 비번+생성시간 비교
            return Hash::check($_user_pw, $user->user_pw.$user->created_at);
        }
        else
            return Hash::check($_user_pw, $user->user_pw);
    }
}
