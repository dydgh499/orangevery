<?php
namespace App\Http\Controllers\Message;

use App\Http\Traits\Models\EncryptDataTrait;

enum AuthPhoneNumReturnType: int {
    case SUCCESS = 1;
    case TOKEN_EMPTY = 3;
    case TOKEN_AES_DECODE_FAIL  = 4;
    case TOKEN_CONTENT_WRONG    = 5;
}

class AuthPhoneNum
{
    use EncryptDataTrait;

    static function tokenDecode($token)
    {
        $inst = new AuthPhoneNum();
        return $inst->aes256_decode($token);
    }

    static function validate($token)
    {
        if($token === '')
            return AuthPhoneNumReturnType::TOKEN_EMPTY->value;
        else
        {
            $auth_info = self::tokenDecode($token);
            if($auth_info)
            {
                $token_info = json_decode($auth_info, true);
                if(isset($token_info['phone_num']) && isset($token_info['verify_code']) && isset($token_info['verify_date']))
                    return AuthPhoneNumReturnType::SUCCESS->value;
                else
                    return AuthPhoneNumReturnType::TOKEN_CONTENT_WRONG->value;
            }
            else
                return AuthPhoneNumReturnType::TOKEN_AES_DECODE_FAIL->value;
        }
    }

}
