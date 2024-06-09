<?php
namespace App\Http\Controllers\Auth;

use App\Models\Salesforce;
use App\Models\Merchandise;

use Illuminate\Support\Facades\Redis;
use App\Http\Traits\Models\EncryptDataTrait;
use App\Enums\AuthLoginCode;

use Illuminate\Support\Facades\Hash;

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
            if(Hash::check($user_pw, $user->user_pw))
            {
                $result['result'] = AuthLoginCode::NOT_ALLOW_FIRST_PASSWORD->value;
                $result['msg']    = '초기 패스워드로 업데이트할 수 없습니다.';
            }
            else
            {
                $user->user_pw = Hash::make($user_pw);
                $user->password_change_at = date('Y-m-d H:i:s');
                $user->save();
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
}
