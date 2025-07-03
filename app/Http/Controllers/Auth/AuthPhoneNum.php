<?php
namespace App\Http\Controllers\Auth;

use App\Enums\AuthLoginCode;
use App\Http\Traits\Models\EncryptDataTrait;
use Carbon\Carbon;

class AuthPhoneNum
{
    use EncryptDataTrait;

    static function tokenDecode($token)
    {
        $inst = new AuthPhoneNum();
        return $inst->aes256_decode($token);
    }

    // 휴대폰 토큰 검증
    static function validate($token)
    {
        if($token === '')
            return AuthLoginCode::REQUIRE_PHONE_AUTH->value;
        else
        {
            $auth_info = self::tokenDecode($token);
            if($auth_info)
            {
                $token_info = json_decode($auth_info, true);
                if(isset($token_info['phone_num']) && isset($token_info['verify_code']) && isset($token_info['verify_date']))
                {
                    $verify_date = Carbon::createFromFormat('Y-m-d H:i:s', $token_info['verify_date'])->addMinutes(5);
                    if (Carbon::now()->lessThanOrEqualTo($verify_date))
                        return AuthLoginCode::SUCCESS->value;
                    else
                        return AuthLoginCode::EXPIRED_TOKEN->value;
                }
                else
                    return AuthLoginCode::WRONG_ACCESS->value;
            }
            else
                return AuthLoginCode::WRONG_ACCESS->value;
        }
    }
}
