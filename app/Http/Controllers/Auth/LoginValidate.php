<?php
namespace App\Http\Controllers\Auth;

use App\Enums\AuthLoginCode;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Auth\AuthGoogleOTP;

use App\Http\Controllers\Auth\AuthAccountLock;

use App\Http\Controllers\Auth\AuthOperatorIP;
use App\Http\Controllers\Auth\AuthPasswordChange;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Ablilty\ShoppingMallWindowInterface;

use Illuminate\Support\Facades\Hash;
use App\Enums\HistoryType;

class LoginValidate
{
    static protected function isMerchant($result) 
    {
        return isset($result['user']->mcht_name) ? true : false;
    }

    static protected function setMerchant($result) 
    {
        $result['user']->level = 10;
        if(self::merchantStatusValidate($result) === false)
            $result['result'] = AuthLoginCode::INHIBITION_ACCOUNT->value;
        else
        {
            if(count($result['user']->shoppingMall) === 0)
                $result['user']->shoppingMall = ShoppingMallWindowInterface::renew($result['user']->id);
        }
        return $result;
    }

    static protected function isLockAccount($result) 
    {
        return $result['user']->is_lock ? true : false;
    }

    static protected function isCorrectPassword($result, $user_pw)
    {
        return AuthPasswordChange::HashCheck($result['user'], $user_pw) ? true : false;
    }

    static protected function merchantStatusValidate($result)
    {
        return $result['user']->merchant_status === 2 ? false : true;
    }

    static protected function locationValidate($result, $ip)
    {
        if($result['user']->level >= 35 && AuthOperatorIP::valiate($result['user']->brand_id, $ip === false))
            AbnormalConnection::tryNoRegisterIP($result['user']);
    }

    static protected function secondAuthValidate($result, $request)
    {
        $brand = BrandInfo::getBrandById($result['user']->brand_id);
        if($result['user']->level >= 35)
        {
            // 3FA
            if(AuthOperatorIP::valiate($result['user']->brand_id, $request->ip()))
            {   // 2FA
                if($result['user']->google_2fa_secret_key)
                    return AuthGoogleOTP::validate($request->token);
                else if($brand['pv_options']['paid']['use_head_office_withdraw'])
                    return AuthPhoneNum::validate($request->token);   // 휴대폰 인증
                else
                    return AuthLoginCode::SUCCESS->value;
            }
            else
            {
                AbnormalConnection::tryNoRegisterIP($result['user']);
                return AuthLoginCode::NOT_FOUND->value;
            }
        }
        else
        {
            if($brand['pv_options']['free']['secure']['login_only_operate'])
                return AuthLoginCode::NOT_FOUND->value;
            if($result['user']->password_change_at === null && $request->is('*/v1/bf/sign-in') === false)
                return AuthLoginCode::REQUIRE_PASSWORD_CHANGE->value;
            else
            {
                if($result['user']->google_2fa_secret_key)
                    return AuthGoogleOTP::validate($request->token);
                else
                    return AuthLoginCode::SUCCESS->value;
            }
        }
    }
}
