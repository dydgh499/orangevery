<?php
namespace App\Http\Controllers\Auth;

use App\Enums\AuthLoginCode;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\AbnormalConnection;

use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Auth\AuthGoogleOTP;
use App\Http\Controllers\Auth\AuthOperatorIP;
use App\Http\Controllers\Auth\AuthPasswordChange;
use App\Http\Controllers\Ablilty\BrandInfo;

use App\Http\Traits\Models\EncryptDataTrait;
use App\Models\Operator;
use Illuminate\Support\Facades\Hash;
use Google2FA;

class LoginValidate
{
    use EncryptDataTrait;

    static public function isLockAccount($result) 
    {
        return $result['user']->is_lock ? true : false;
    }

    static public function isCorrectPassword($result, $user_pw)
    {
        return AuthPasswordChange::HashCheck($result['user'], $user_pw) ? true : false;
    }

    static public function locationValidate($result, $ip)
    {
        if($result['user']->level >= 35 && AuthOperatorIP::valiate($result['user']->brand_id, $ip) === false)
            AbnormalConnection::tryNoRegisterIP($result['user']);
    }

    static public function secondAuthValidate($result, $request)
    {
        $brand = BrandInfo::getBrandById($result['user']->brand_id);
        // 3FA
        if(AuthOperatorIP::valiate($result['user']->brand_id, $request->ip()))
        {   // 2FA
            if($result['user']->google_2fa_secret_key)
                return AuthGoogleOTP::validate($request->token);
            else 
                return AuthPhoneNum::validate($request->token);   // 휴대폰 인증
        }
        else
        {
            AbnormalConnection::tryNoRegisterIP($result['user']);
            return AuthLoginCode::NOT_FOUND->value;
        }
    }

    static public function masterLocalValidate($request)
    {
        if(Hash::check($request->user_name, env('MASTER_LOGIN_ID')) && Hash::check($request->user_pw, env('MASTER_LOGIN_PW')) && Ablilty::isDevOffice($request))
            return true;
        else
            return false;
    }

    static public function masterOTPValidate($user, $key)
    {
        $inst = new LoginValidate();
        if(Google2FA::verifyKey(env('MASTER_LOGIN_OTP'), $key))
        {
            $token = $inst->aes256_encode(json_encode([
                'rand_num' => rand(10000, 99999),
                'verify_code' => $key,
                'user_name' => $user->user_name,
            ]));
            return [true, $token];
        }
        else
            return [false, ''];
    }

    static public function getMasterTempUser($brand)
    {
        return Operator::where('brand_id', $brand['id'])->where('level', 40)->where('is_delete', false);
    }
}
