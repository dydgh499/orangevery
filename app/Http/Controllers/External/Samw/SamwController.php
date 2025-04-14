<?php

namespace App\Http\Controllers\External\Samw;

use App\Http\Controllers\Manager\Withdraws\VirtualAccountHistoryController;

use App\Models\Merchandise;
use App\Models\Withdraws\VirtualAccountHistory;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\LoginRequest;
use App\Http\Requests\Manager\Settle\CollectWithdrawRequestV2;

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\AuthExternalApiEnableIP;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group SAMW API
 * 
 * 본 API를 이용하기 위해서 IP 등록요청이 필요합니다.
 */
class SamwController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    /**
     * 로그인
     * 
     * @unauthenticated
     * 
     * @header External-Api Bearer {API_KEY}
     * @responseFile 200 storage/bf/login.json
     * @responseField access_token string Bearer 토큰 값
     * @responseField user object 유저정보
     */
    public function login(LoginRequest $request)
    {
        $result = Login::isSafeLogin(new Merchandise, $request);    // check merchandise
        if($result['result'] === 0)
        {
            $ip             = $request->ip();
            $external_api   = str_replace("Bearer ", "", $request->header('External-Api'));
            $json           = AuthExternalApiEnableIP::get($result['user']);

            if(in_array($ip, $json['ips']) && $json['api_key'] === $external_api)
            {
                $data = $result['user']->loginAPI(10);
                $data['user'] = [
                    'id'        => $data['user']->id,
                    'user_name' => $data['user']->user_name,
                    'mcht_name' => $data['user']->mcht_name,
                    'level'     => 10,
                ];
                return $this->response(0, $data);
            }
            else
                return $this->extendResponse(9999, '허용된 인증정보가 아닙니다');
        }
        else if($result['result'] === -1)
            return $this->extendResponse(1000, __('auth.not_found_obj'));
        else
            return $this->extendResponse($result['result'], $result['msg'], $result['data']);
    }

    /**
     * 출금가능금액 조회
     *
     * 출금가능한금액을 조회합니다.<br>즉시 출금 결제모듈의 매출은 포함되지 않습니다.
     * @header External-Api Bearer {API_KEY}
     * @queryParam samw_code string required SAMW CODE Example: 2BWHVKQS7P
     * @responseFile 200 storage/bf/withdrawsBalance.json
     * @responseField profit integer 출금가능한도
     */
    public function withdrawsBalance(Request $request)
    {
        $inst = new VirtualAccountHistoryController(new VirtualAccountHistory);
        return $inst->withdrawsBalance($request);
    }

    /**
     * 출금요청
     *
     * 출금가능한금액을 조회합니다.<br>암호화 예시: base64_encode(openssl_encrypt(a, "AES-256-CBC", enc_key, true, iv))
     * @header External-Api Bearer {API_KEY}
     * @responseFile 201 storage/bf/withdrawsStore.json
     * @responseField id integer 출금요청 고유번호
     */
    public function withdrawsStore(CollectWithdrawRequestV2 $request)
    {
        $inst = new VirtualAccountHistoryController(new VirtualAccountHistory);
        return $inst->collectWithdrawCustom($request);
    }
}
