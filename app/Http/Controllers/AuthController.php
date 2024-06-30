<?php

namespace App\Http\Controllers;

use App\Enums\AuthLoginCode;

use App\Models\Brand;
use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Operator;

use Illuminate\Http\Request;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Ablilty\IPInfo;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthPasswordChange;

use App\Http\Requests\Manager\LoginRequest;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Auth\Login;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

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
    public function domain(Request $request)
    {
        $result = IPInfo::validate($request);
        if($result === false)
        {
            $msg = 'Abnormal access has been detected. The access log will be sent to the administrator and analyzed.';
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
            return response(view('application', ['json' => $brand, 'ip'=>$request->ip()]))
                ->withCookie('XSRF-TOKEN', csrf_token());
        }
        else
            return $this->response(1000);
    }
    
    /*
    * 패스워드 변경(초기화)
    */
    public function resetPassword(Request $request)
    {
        $request->validate(['token' => 'required', 'user_pw' => 'required', 'level'=>'required|numeric']);

        $result = AuthPasswordChange::getTokenContent($request->token);
        if($result['result'] === AuthLoginCode::SUCCESS->value)
        {
            $result = AuthPasswordChange::updateFirstPassword($result, $request->user_pw);
            if($result['result'] === AuthLoginCode::SUCCESS->value)
                return $this->response(0, $result['data'])->withHeaders($this->tokenableExpire());
            else
                return $this->extendResponse($result['result'], $result['msg'], []);
        }
        else
            return $this->extendResponse($result['result'], $result['msg'], []);
    }

    /*
    * 패스워드 1달 연장(가맹점, 영업점, 운영자)
    */
    public function extendPasswordAt(Request $request)
    {
        $params = ['password_change_at' => Carbon::now()->addMonthNoOverflow(1)->format('Y-m-d H:i:s')];
        if(Ablilty::isMerchandise($request))
            $orm = new Merchandise;
        else if(Ablilty::isSalesforce($request))
            $orm = new Salesforce;
        else
            $orm = new Operator;

        $orm->where('id', $request->user()->id)->update($params);
        return $this->response(1, $params);
    }

    /**
     * 로그인(관리자)
     * @unauthenticated
     *
     * @bodyParam brand_id integer required 브랜드 ID Example: 1
     */
    public function signIn(LoginRequest $request)
    {
        $result = Login::isSafeAccount(new Operator(), $request);    // check operator
        if($result !== null)
            return $result;
        
        $result = Login::isSafeAccount(new Salesforce(), $request);    // check sales
        if($result !== null)
            return $result;

        $result = Login::isSafeAccount(new Merchandise(), $request);    // check merchandise
        if($result !== null)
            return $result;
        else
        {
            $query = Operator::where('brand_id', $request->brand_id)->where('level', 40)->where('is_delete', false);
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
            if(Operator::where('brand_id')->where('level', 40)->where('is_delete', false)->exists())
                return $this->extendResponse(951, '잘못된 접근입니다.', []);
            $res = Brand::where('id', $request->brand_id)
                ->update([
                    'ceo_name'=>$request->ceo_name,
                    'phone_num'=>$request->phone_num,
                    'business_num'=>$request->business_num,
                ]);
            $current = date("Y-m-d H:i:s");
            $res = Operator::create([
                'brand_id'  => $request->brand_id,
                'user_name' => $request->user_name,
                'user_pw'   => Hash::make($request->user_pw.$current),
                'nick_name' => '본사',
                'profile_img'   => '/build/assets/avatar_5.644eef84.svg',
                'phone_num'     =>$request->phone_num,
                'level'         => 40,
                'created_at'    => $current,
            ]);
            $user = Operator::where('id', $res->id)->first();
            return $this->response(0, $user->loginInfo(40));
        }, 3);
    }

    /*
    * 예금주 조회
    */
    public function onwerCheck(Request $request)
    {
        $data = $request->all();
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/onwer-check';
        $res = post($url, $data);
        if($res['body']['result'] === 100)
            return $this->response(1, ['message'=> $res['body']['message']]);
        else
            return $this->extendResponse(1999, $res['body']['message'], $res['body']['data']);
    }
}

