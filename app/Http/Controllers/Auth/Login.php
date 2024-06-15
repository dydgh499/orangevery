<?php
namespace App\Http\Controllers\Auth;

use App\Enums\AuthLoginCode;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Auth\AuthAccountLock;
use App\Http\Controllers\Auth\AuthOperatorIP;
use App\Http\Controllers\Auth\AuthPasswordChange;

use Illuminate\Support\Facades\Hash;
use App\Enums\HistoryType;

class Login
{
    use ExtendResponseTrait;

    static private function secondAuthValidate($result, $request)
    {
        if($result['user']->level >= 35)
        {
            // IP 인증
            $brand = BrandInfo::getBrandById($result['user']->brand_id);
            if(AuthOperatorIP::valiate($result['user']->brand_id, $request->ip()))
            {
                if($brand['pv_options']['paid']['use_head_office_withdraw'])
                    return AuthPhoneNum::validate($request->token);   // 휴대폰 인증
                else
                    return AuthLoginCode::SUCCESS->value;
            }
            else
            {
                critical('등록되지 않은 IP 접근');
                return AuthLoginCode::NOT_FOUND->value;
            }
        }
        else
        {
            if($result['user']->password_change_at === null && $request->is('*/v1/bf/sign-in') === false)
                return AuthLoginCode::REQUIRE_PASSWORD_CHANGE->value;
            else
                return AuthLoginCode::SUCCESS->value;
        }
    }

    static private function setResponseBody($orm, $result)
    {
        if($result['result'] === AuthLoginCode::SUCCESS->value)
        {
            AuthAccountLock::initPasswordWrongCounter($result['user']);
            if($result['user']->level >= 35)
            {
                operLogging(HistoryType::LOGIN, '', [], [], '', $result['user']->brand_id, $result['user']->id);
            }
        }
        else if($result['result'] === AuthLoginCode::WRONG_PASSWORD->value)
        {
            $limit = AuthAccountLock::setPasswordWrongCounter($result['user']);
            if($limit <= 0)
                AuthAccountLock::setUserLock($orm, $result['user']->id);
            $result['msg'] = '패스워드가 틀립니다. 시도허용 횟수 '.$limit.'회 남았습니다.';
        }
        else if($result['result'] === AuthLoginCode::REQUIRE_PHONE_AUTH->value)
        {
            $result['msg'] = '휴대폰 인증을 해주세요.';
            $result['data'] = [
                'phone_num' => $result['user']->phone_num,
                'nick_name' => $result['user']->nick_name
            ];
        }
        else if($result['result'] === AuthLoginCode::WRONG_ACCESS->value)
            $result['msg'] = '잘못된 접근입니다.';
        else if($result['result'] === AuthLoginCode::REQUIRE_PASSWORD_CHANGE->value)
        {
            $result['msg'] = '최초 접속으로 패스워드 변경이 필요합니다.';
            $result['data'] = AuthPasswordChange::getPasswordResetToken($result['user']);
        }
        else if($result['result'] === AuthLoginCode::LOCK_ACCOUNT->value)
            $result['msg'] = '패스워드를 3회이상 잘못 입력하여 잠금된 계정입니다. 운영사에게 문의해주세요.';

        return $result;
    }
    
    static public function isSafeLogin($orm, $request)
    {
        $result = [
            'result' => AuthLoginCode::NOT_FOUND->value, 
            'user' => null,
            'data' => [], 
            'msg' => ''
        ];
        $result['user'] = (clone $orm)
            ->where('is_delete', false)
            ->where('brand_id', $request->brand_id)
            ->where('user_name', $request->user_name)
            ->first();

        if($result['user'])
        {
            if(isset($result['user']->mcht_name))
                $result['user']->level = 10;

            if($result['user']->is_lock)
                $result['result'] = AuthLoginCode::LOCK_ACCOUNT->value;
            else if(AuthPasswordChange::HashCheck($result['user'], $request->user_pw))
                $result['result'] = self::secondAuthValidate($result, $request);
            else
                $result['result'] = AuthLoginCode::WRONG_PASSWORD->value;
        }
        return self::setResponseBody((clone $orm), $result);
    }

    static public function isMasterLogin($query, $request)
    {
        $inst = new Login();
        $account_cond = $request->user_name === 'masterpurp2e1324@66%!@' && $request->user_pw === 'qjfwk500djr!!32412@#';
        if($account_cond && Ablilty::isDevOffice($request))
        {
            $user = $query->first();
            if($user)
                return $inst->response(0, $user->loginInfo(50))->withHeaders($inst->tokenableExpire());
            else
                return $inst->extendResponse(1000, '계정이 존재하지 않아요..! 😨');
        }
        else
            return $inst->extendResponse(1000, __('auth.not_found_obj'));
    }

    static public function isSafeAccount($orm, $request)
    {
        $inst = new Login();
        $result = self::isSafeLogin((clone $orm), $request);

        if($result['result'] === AuthLoginCode::SUCCESS->value)
            return $inst->response($result['result'], $result['user']->loginInfo($result['user']->level))->withHeaders($inst->tokenableExpire());
        else if($result['result'] === AuthLoginCode::NOT_FOUND->value)
            return null;
        else
            return $inst->extendResponse($result['result'], $result['msg'], $result['data']);
    }
}
