<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthGoogleOTP;
use App\Http\Controllers\Manager\Service\OperatorIPController;
use App\Enums\AuthLoginCode;

use App\Models\Brand;
use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Operator;
use App\Models\Gmid;

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
            $use_bonaeja = $brand['pv_options']['free']['bonaeja']['user_id'] !== '' ? true : false;
            $brand['pv_options']['free']['bonaeja'] = [];
            $brand['pv_options']['free']['bonaeja']['min_balance_limit'] = 0;
            $brand['pv_options']['free']['bonaeja'] = [
                'min_balance_limit' => $brand['pv_options']['free']['bonaeja']['min_balance_limit'],
                'is_use' => $use_bonaeja,
            ];
            unset($brand['pv_options']['p2p']);
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
        $request->validate(['token' => 'required', 'user_pw' => 'required', 'level'=>'required|integer']);

        $result = AuthPasswordChange::getTokenContent($request->token);
        if($result['result'] === AuthLoginCode::SUCCESS->value)
        {
            $result = AuthPasswordChange::updateFirstPassword($result, $request->user_pw);
            if($result['result'] === AuthLoginCode::SUCCESS->value)
            {
                $info = $result['user']->loginInfo($result['user']->level);
                return $this->response(0, $info)->withHeaders($this->tokenableExpire());
            }
            else
                return $this->extendResponse($result['result'], $result['msg'], []);
        }
        else
            return $this->extendResponse($result['result'], $result['msg'], []);
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
        $sales_with = [];
        if($brand['pv_options']['paid']['brand_mode'] === 1)
            $sales_with[] = 'salesRecommenderCodes';

        $result = Login::isSafeAccount(Operator::where('is_active', true), $request);    // check operator
        if($result !== null)
            return $result;
        
        $result = Login::isSafeAccount(Salesforce::with($sales_with), $request);    // check sales
        if($result !== null)
            return $result;

        $result = Login::isSafeAccount(new Gmid(), $request);    // check sales
        if($result !== null)
            return $result;

        $result = Login::isSafeAccount(Merchandise::with(['onlinePays.payWindows', 'shoppingMall']), $request);    // check merchandise
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
            if(Operator::where('brand_id', $request->brand_id)->where('level', 40)->where('is_delete', false)->exists())
                return $this->response(951);
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
            OperatorIPController::addIP($request->brand_id, $request->ip());
            return $this->response(0, $user->loginInfo(40));
        }, 3);
    }

    public function vertify2FA(Request $request)
    {
        $vertifyUser = function($orm, $request) {
            $result = Login::isSafeLogin($orm, $request);
            if($result['result'] !== -1)
            {
                [$result, $token] = AuthGoogleOTP::verify($result['user'], $request->verify_code);
                if($result)
                    return $this->response(0, ['token'=>$token]);
                else
                    return $this->response(952);
            }
            else
                return false;    
        };

        $result = $vertifyUser(new Operator(), $request);
        if($result !== false)
            return $result;

        $result = $vertifyUser(new Salesforce(), $request);
        if($result !== false)
            return $result;

        $result = $vertifyUser(new Merchandise(), $request);
        if($result !== false)
            return $result;

        return $this->response(951);
    }
}

