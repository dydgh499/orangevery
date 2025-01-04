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
        
        $orm = $result['data']['level'] === 10 ? Merchandise::with(['onlinePays.payWindows', 'shoppingMall']) : new Salesforce;
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

        if($created_at->greaterThanOrEqualTo($reference_time))
        {   // 계정생성일이 reference_time 보다 높을때
            return Hash::check($_user_pw.$user->created_at, $user->user_pw);
        }
        else if($user->password_change_at && $password_change_at->greaterThanOrEqualTo($reference_time))
        {   // 패스워드를 변경한 적이 있고, 패스워드 변경일이 reference_time 보다 높을때
            return Hash::check($_user_pw.$user->created_at, $user->user_pw);
        }
        else
            return Hash::check($_user_pw, $user->user_pw);
    }
    

    static function passwordValidate($user_name, $password)
    {
        $pattern = '/(\d)\1{2,}|0123|1234|2345|3456|4567|5678|6789|7890/';
        if(preg_match($pattern, $password) && request()->user()->brand_id != 30) // 픽스플러스 제외
            return [false, '패스워드에 연속된 숫자나 중복된 숫자(3개 이상)를 포함할 수 없습니다.'];
        else if(strpos($password, $user_name) !== false)
            return [false, '패스워드에 ID를 포함할 수 없습니다.'];
        else
            return [true, ''];
    }

    static public function userNameValidate($user_name)
    {
        $ban_words = ['test', 'admin', 'master', 'user', 'pay'];
        foreach($ban_words as $ban_word)
        {
            if(strpos($user_name, $ban_word) !== false)
                return [false, "ID에 포함할 수 없는 단어가 존재합니다.($ban_word)"];
        }
        return [true, ''];
    }

    static public function registerValidate($user_name, $password)
    {
        [$result, $msg] = self::userNameValidate($user_name);
        if($result === false)
            return [$result, $msg];
        else
            return self::passwordValidate($user_name, $password);
    }
}
