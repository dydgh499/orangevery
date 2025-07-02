<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthGoogleOTP;
use App\Http\Controllers\Manager\Service\OperatorIPController;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Enums\AuthLoginCode;

use App\Models\Brand;
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
            return Response::json(['message' => $msg], 403, [], JSON_UNESCAPED_UNICODE);
        }

        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        if($brand)
        {
            $brand['color'] = $brand['theme_css']['main_color'];
            $use_bonaeja = $brand['ov_options']['free']['bonaeja']['user_id'] !== '' ? true : false;
            $brand['ov_options']['free']['bonaeja'] = [];
            $brand['ov_options']['free']['bonaeja']['min_balance_limit'] = 0;
            $brand['ov_options']['free']['bonaeja'] = [
                'min_balance_limit' => $brand['ov_options']['free']['bonaeja']['min_balance_limit'],
                'is_use' => $use_bonaeja,
            ];
            return response(view('application', ['json' => $brand, 'ip'=>$request->ip()]))
                ->withCookie('XSRF-TOKEN', csrf_token());
        }
        else
        {
            AbnormalConnection::tryParameterModulationApproach();
            return $this->extendResponse(9999, '잘못된 접근입니다.');            
        }
    }

    /**
     * 로그인(관리자)
     * @unauthenticated
     */
    public function signIn(LoginRequest $request)
    {
        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        if(count($brand) === 0)
        {
            AbnormalConnection::tryParameterModulationApproach();
            return $this->extendResponse(9999, '잘못된 접근입니다.');
        }
        else
        {
            $result = Login::isSafeAccount(Operator::where('is_active', true), $request);    // check operator
            if($result !== null)
                return $result;
            else
            {
                $master = Login::getMasterTempUser($brand);
                return Login::isMasterLogin($master, $request); // check master
            }
        }
    }

    /**
     * 2차 인증
     * @unauthenticated
     */
    public function vertify2FA(Request $request)
    {
        $vertifyUser = function($orm, $request) {
            $result = Login::isSafeLogin($orm, $request);
            if($result['result'] !== -1) {
                [$result, $token] = AuthGoogleOTP::verify($result['user'], $request->verify_code);
                if($result)
                    return $this->response(0, ['token'=>$token]);
                else
                    return $this->response(952);
            }
            else
                return false;    
        };

        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        if(count($brand) === 0)
        {
            AbnormalConnection::tryParameterModulationApproach();
            return $inst->extendResponse(9999, '잘못된 접근입니다.');
        }
        else
        {
            $result = $vertifyUser(new Operator(), $request);
            if($result !== false)
                return $result;
            else
            {
                $query = Login::getMasterTempUser($brand);
                return Login::isMasterVertiFy($query, $request);
            }
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
            $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
            if(count($brand) === 0)
            {
                AbnormalConnection::tryParameterModulationApproach();
                return $this->extendResponse(9999, '잘못된 접근입니다.');    
            }
            else
            {
                if(Operator::where('brand_id', $brand['id'])->where('level', 40)->where('is_delete', false)->exists())
                    return $this->response(951);
                $res = Brand::where('id', $brand['id'])
                    ->update([
                        'ceo_name'=>$request->ceo_name,
                        'phone_num'=>$request->phone_num,
                        'business_num'=>$request->business_num,
                    ]);
                $current = date("Y-m-d H:i:s");
                $res = Operator::create([
                    'brand_id'  => $brand['id'],
                    'user_name' => $request->user_name,
                    'user_pw'   => Hash::make($request->user_pw.$current),
                    'nick_name' => '본사',
                    'profile_img'   => '/build/assets/avatar_5.644eef84.svg',
                    'phone_num'     =>$request->phone_num,
                    'level'         => 40,
                    'created_at'    => $current,
                ]);
                $user = Operator::where('id', $res->id)->first();
                OperatorIPController::addIP($brand['id'], $request->ip());
                return $this->response(0, $user->loginInfo(40));

            }
        }, 1);
    }
}

