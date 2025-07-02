<?php

namespace App\Http\Controllers\Message;

use App\Models\Brand;
use App\Models\Operator;
use App\Models\Merchandise;

use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;

use App\Enums\AuthLoginCode;
use App\Http\Controllers\Ablilty\SMS;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Utils\Comm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

/**
 * @group Message API
 *
 * 문자 발송 API 입니다.
 */
class MessageController extends Controller
{
    use ExtendResponseTrait, EncryptDataTrait;

    public function index(Request $request)
    {
        return $this->response(0, [
            'page' => 1,
            'page_size' => 20,
            'total' => 0,
            'content' => []
        ]);
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        if($brand)
        {
            $bonaeja = $brand['ov_options']['free']['bonaeja'];
            $params = [
                'user_id'   => $bonaeja['user_id'],
                'api_key'   => $bonaeja['api_key'],
                'page'      => $request->page,
                'page_size' => $request->page_size,
                's_dt'      => $request->s_dt,
                'e_dt'      => $request->e_dt,
                'search'    => $request->search,
            ];
            $res = Comm::post("https://api.bonaeja.com/api/msg/v1/list", $params);
            if($res['code'] == 500)
                return $this->extendResponse(1000, '통신 과정에서 에러가 발생했습니다.');
            else
                return $this->response(0, $res['body']['data']);
        }
    }

    /*
     * 잔액 조회
     */
    public function chart(Request $request)
    {
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        if($brand)
        {
            $bonaeja = $brand['ov_options']['free']['bonaeja'];
            $params = [
                'user_id'   => $bonaeja['user_id'],
                'api_key'   => $bonaeja['api_key'],
            ];
            $res = Comm::post("https://api.bonaeja.com/api/msg/v1/remain", $params);
            if($res['code'] == 500)
                return $this->extendResponse(1000, '통신 과정에서 에러가 발생했습니다.');
            else
                return $this->response(0, $res['body']);
        }
    }

    public function send($phone_num, $message, $brand_id)
    {
        $brand = BrandInfo::getBrandById($brand_id);
        if($brand)
        {
            $bonaeja = $brand['ov_options']['free']['bonaeja'];
            
            if($bonaeja['user_id'])
            {
                $sms = [
                    'user_id'   => $bonaeja['user_id'],
                    'sender'    => $bonaeja['sender_phone'],
                    'api_key'   => $bonaeja['api_key'],
                    'receiver'  => $phone_num,
                    'msg'       =>  $message,
                ];
                return Comm::post("https://api.bonaeja.com/api/msg/v1/send", $sms);
                
            }
            else
                return null;
        }
    }
    
    /*
    * SMS 문자발송
    */
    public function smslinkSend(Request $request)
    {
        $validated = $request->validate(['buyer_phone'=>'required']);
        $message = $request->buyer_name."님\n아래 url로 접속해 결제를 진행해주세요.\n\n".$request->url;
        $res = SMS::send($request->buyer_phone, $message, $request->user()->brand_id);

        if($res === null)
            return $this->extendResponse(1999, '문자발송 플랫폼과 연동되어있지 않습니다.<br>계약 이후 사용 가능합니다.');
        else if($res['code'] == 500)
            return $this->extendResponse(1999, '통신 과정에서 에러가 발생했습니다.');
        else
        {
            SMS::validate($request->user()->brand_id);
            return $this->extendResponse($res['body']['code'] == 100 ? 0 : 1999, $res['body']['message']);
        }
    }

    /*
    * 모바일 인증코드 발송
    */
    public function mobileCodeSend($brand_id, $phone_num, $mcht_id)
    {
        $brand = BrandInfo::getBrandById($brand_id);
        if($brand)
        {
            if($mcht_id === -1)
            {
                $rand = random_int(100000, 999999);
                $message = "인증번호 [$rand]을(를) 입력해주세요";
                $res = SMS::send($phone_num, $message, $brand_id);
                if($res == null)
                    return [1999, '문자발송 플랫폼과 연동되어있지 않습니다. 계약 이후 사용 가능합니다.'];
                else if($res['code'] == 500)
                    return [1999, '통신 과정에서 에러가 발생했습니다.'];
                else
                {
                    SMS::validate($brand_id);
                    if($res['body']['code'] === 100)
                        Redis::set("verify-code:".$phone_num, $rand, 'EX', 180);
                    return [$res['body']['code'] == 100 ? 0 : 1999, $res['body']['message']];
                }
            }
            else
                return [1999, '휴대폰 인증허용 회수를 초과하였습니다.'];
        }
        else
            return [951, '권한이 없습니다.'];
    }
    /*
     * 모바일 코드 발급
     */
    public function mobileCodeIssuence(Request $request)
    {
        $validated = $request->validate(['phone_num'=>'required', 'mcht_id'=>'required']);
        $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
        if(count($brand) === 0)
        {
            AbnormalConnection::tryParameterModulationApproach();
            return $this->extendResponse(9999, '잘못된 접근입니다.');   
        }
        else
        {
            [$code, $msg] = $this->mobileCodeSend($brand['id'], $request->phone_num, $request->mcht_id);
            return $this->extendResponse($code, $msg);    
        }
    }

    /**
     * 휴대폰 인증번호 확인
     *
     * 제한시간은 3분이며 3분이후에 생성된 키값이 자동으로 삭제됩니다.
     *
     * @bodyParam verification_number string required 문자로 전달받은 인증번호 Example: 1028933
     * @bodyParam phone_num string required 휴대폰번호 Example: 01000000000
    */
    public function mobileCodeAuth(Request $request)
    {
        $validated = $request->validate(['verification_number'=>'required|string', 'phone_num'=>'required|string']);
        $phone_num = $request->phone_num;
        $verification_number = Redis::get("verify-code:".$phone_num);

        $cond_1 = $request->verification_number === $verification_number;
        if($cond_1)
        {
            $token = json_encode([
                'phone_num' => $request->phone_num,
                'verify_code' => $verification_number,
                'verify_date' => date('Y-m-d H:i:s')
            ]);

            if($request->mcht_id !== null)
            {
                $brand = BrandInfo::getBrandByDNS($_SERVER['HTTP_HOST']);
                if(count($brand) === 0)
                {
                    AbnormalConnection::tryParameterModulationApproach();
                    return $this->extendResponse(9999, '잘못된 접근입니다.');   
                }
                else
                    AuthPhoneNum::clearValidate($brand, $request->mcht_id, $phone_num);
            }
            return $this->response(0,  ['token' => $this->aes256_encode($token)]);
        }
        else
            return $this->extendResponse(1000, __('auth.failed_token'), []);
    }

    /**
     * 본사등급 휴대폰번호 인증 요청
    */
    public function headOfficeMobileCodeIssuence(Request $request)
    {
        $validated = $request->validate(['user_name'=>'required|string']);

        $query = Operator::where('brand_id', $request->user()->brand_id)->where('is_delete', false);
        $head_office_query = (clone $query)->where('level', 40);
        if($request->user()->level === 40)
            $head_office = $head_office_query->where('id', $request->user()->id)->first();
        else
            $head_office = $head_office_query->first();
        
        $employee = (clone $query)->where('user_name', $request->user_name)->first();

        if(Ablilty::isBrandCheck($request, $employee->brand_id) === false)
            return $this->response(951);
        else
        {
            [$code, $msg] = $this->mobileCodeSend($head_office->brand_id, $head_office->phone_num, -1);
            if($code === 0)
            {
                $msg = $head_office->nick_name.'님에게 인증번호를 보냈습니다.<br>발송된 인증번호를 전달받아 입력해주세요.';
                return $this->extendResponse($code, $msg, [
                    'phone_num' => $head_office->phone_num,
                    'nick_name' => $head_office->nick_name
                ]);
            }
            else
                return $this->extendResponse($code, $msg);
        }
    }

    /**
     * 본사 휴대폰번호 인증 검증
    */
    static public function operatorPhoneValidate($request)
    {
        $result = AuthLoginCode::SUCCESS->value;
        $msg    = '';
        $data   = [];
        
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        if($brand['ov_options']['free']['bonaeja']['user_id'] !== '')
        {
            $result = AuthPhoneNum::validate((string)$request->token);
            if($result === AuthLoginCode::REQUIRE_PHONE_AUTH->value)
                $msg = '휴대폰번호 인증이 필요합니다.'; //잘못된 접근
            else if($result === AuthLoginCode::WRONG_ACCESS->value)
                $msg = '잘못된 접근입니다.';
            else if($result === AuthLoginCode::EXPIRED_TOKEN->value)
                $msg = '만료된 인증입니다. 다시 인증을 시도해주세요.';
        }
        return [$result, $msg, $data];
    }
}
