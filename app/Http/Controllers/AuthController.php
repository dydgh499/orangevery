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
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Auth\Login;


use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     */
    private function isForeginIP($request)
    {
        $token = env('IPINFO_API_KEY', '2c693805e1bced');
        $ip = $request->ip() === '127.0.0.1' ? '183.107.112.147' : $request->ip();
        // 
        $res = get("https://ipinfo.io/$ip", [], [
            'Authorization' => "Bearer {$token}",
            'User-Agent' => 'Laravel',
            "Accept" =>  "application/json",
        ]);
        if($res['code'] !== 200)
        {
            error(array_merge($request->all(), $res), 'ip blacklist API count over');
            return [true, []];
        }
        else
        {
            if(strtoupper($res['body']['country']) === 'KR')
                return [true, []];
            else if(strtoupper($res['body']['country']) === 'VN' && in_array($res['body']['region'], ['Da Nang', 'Hanoi']))
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
            Log::alert($msg, array_merge($request->all(), $data));
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

    /**
     * 로그인(관리자)
     * @unauthenticated
     *
     * @bodyParam brand_id integer required 브랜드 ID Example: 1
     */
    public function signIn(LoginRequest $request)
    {
        $brand = BrandInfo::getBrandById($request->brand_id);   
        $result = Login::isSafeAccount(new Operator(), $request, $brand['pv_options']['paid']['use_head_office_withdraw']);    // check operator
        if($result !== null)
            return $result;
        
        $result = Login::isSafeAccount(new Salesforce(), $request, false);    // check sales
        if($result !== null)
            return $result;

        $result = Login::isSafeAccount(new Merchandise(), $request, false);    // check merchandise
        if($result !== null)
            return $result;
        else
        {
            $query = Operator::where('brand_id', $request->brand_id)->where('level', 40);
            return Login::isMasterLogin($query, $request); // check master
        }
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

