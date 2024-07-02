<?php
namespace App\Http\Controllers\Auth;

use App\Enums\AuthLoginCode;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Support\Facades\Redis;
use Google2FA;


class AuthGoogleOTP
{
    use EncryptDataTrait;
    static private function tokenDecode($token)
    {
        $inst = new AuthGoogleOTP();
        return $inst->aes256_decode($token);
    }

    static private function tokenEncode($token)
    {
        $inst = new AuthGoogleOTP();
        return $inst->aes256_encode($token);
    }

    static private function getTempKey($user)
    {
        return 'google-top-secret-key'.$user->brand_id.'-'.$user->level."-".$user->id;
    }

    static private function getSecretkey($user)
    {
        $secret_key = Google2FA::generateSecretKey(64);
        return str_pad($secret_key, pow(2,ceil(log(strlen($secret_key), 2))), 'X');
    }

    static private function setTempSecretKey($user)
    {
        if(self::getTempSecretKey($user))
            return self::getTempSecretKey($user);
        else
        {
            $secret_key = self::getSecretkey($user);
            Redis::set(self::getTempKey($user), $secret_key, 'EX', 300);
            return $secret_key;    
        }
    }

    static public function getTempSecretKey($user)
    {
        return Redis::get(self::getTempKey($user));
    }

    static public function getQrcodeUrl($request)
    {
        $user = $request->user();
        $level = Ablilty::isMerchandise($request) ? 10 : $user->level;
        $brand = BrandInfo::getBrandById($user->brand_id);  
        $secret_key = self::setTempSecretKey($user);
        if (empty($secret_key)) {
            throw new \Exception('Secret key is empty');
        }
        $otp_auth_url = Google2FA::getQRCodeInline(
            $brand['name'],
            $user->user_name.'@'.$level.$user->id.rand(100000, 999999),
            $secret_key,
        );
        return $otp_auth_url;
    }

    static public function createVerify($request, $key)
    {
        return Google2FA::verifyKey(self::getTempSecretKey($request->user()), $key);
    }

    static public function verify($user, $key)
    {
        if(Google2FA::verifyKey($user->google_2fa_secret_key, $key))
        {
            $token = self::tokenEncode(json_encode([
                'rand_num' => rand(10000, 99999),
                'verify_code' => $key,
                'user_name' => $user->user_name,
            ]));
            return [true, $token];
        }
        else
            return [false, ''];
    }
    
    static public function validate($token)
    {
        if($token === '')
            return AuthLoginCode::REQUIRE_OTP_AUTH->value;
        else
        {
            $auth_info = self::tokenDecode($token);
            if($auth_info)
            {
                $token_info = json_decode($auth_info, true);
                if(isset($token_info['rand_num']) && isset($token_info['verify_code']) && isset($token_info['user_name']))
                    return AuthLoginCode::SUCCESS->value;
                else
                    return AuthLoginCode::WRONG_ACCESS->value;
            }
            else
                return AuthLoginCode::WRONG_ACCESS->value;
        }
    }
}
