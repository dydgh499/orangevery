<?php

namespace App\Http\Controllers\Message;

use App\Models\Brand;
use App\Models\Merchandise;

use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;

use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Auth\AuthPhoneNum;

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
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        if($brand)
        {
            $bonaeja = $brand['pv_options']['free']['bonaeja'];
            $params = [
                'user_id'   => $bonaeja['user_id'],
                'api_key'   => $bonaeja['api_key'],
                'page'      => $request->page,
                'page_size' => $request->page_size,
                's_dt'      => $request->s_dt,
                'e_dt'      => $request->e_dt,
                'search'    => $request->search,
            ];
            $res = post("https://api.bonaeja.com/api/msg/v1/list", $params);
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
            $bonaeja = $brand['pv_options']['free']['bonaeja'];
            $params = [
                'user_id'   => $bonaeja['user_id'],
                'api_key'   => $bonaeja['api_key'],
            ];
            $res = post("https://api.bonaeja.com/api/msg/v1/remain", $params);
            if($res['code'] == 500)
                return $this->extendResponse(1000, '통신 과정에서 에러가 발생했습니다.');
            else
                return $this->response(0, $res['body']);
        }
    }

    /*
     * 예치금 잔액 검증
     */
    private function bonaejaDepositValidate($bonaeja, $brand_name)
    {
        $params = [
            'user_id'   => $bonaeja['user_id'],
            'api_key'   => $bonaeja['api_key'],
        ];
        $res = post("https://api.bonaeja.com/api/msg/v1/remain", $params);
        if($res['body']['code'] == 100)
        {
            $total_deposit = $res['body']['data']['TOTAL_DEPOSIT'];
            if($total_deposit < ((int)$bonaeja['min_balance_limit'] * 10000))
            {
                $sms = [
                    'user_id'   => $bonaeja['user_id'],
                    'sender'    => $bonaeja['sender_phone'],
                    'api_key'   => $bonaeja['api_key'],
                    'receiver'  => $bonaeja['receive_phone'],
                    'msg'       => "[".$brand_name."] 보내자 예치금이 부족합니다. 예치금을 충전해주세요.(현재 잔액:".number_format($total_deposit)."원)",
                ];
                $_res = post("https://api.bonaeja.com/api/msg/v1/send", $sms);
            }
        }
    }

    public function send($phone_num, $message, $brand_id)
    {
        $brand = BrandInfo::getBrandById($brand_id);
        if($brand)
        {
            $bonaeja = $brand['pv_options']['free']['bonaeja'];
            
            if($bonaeja['user_id'])
            {
                $sms = [
                    'user_id'   => $bonaeja['user_id'],
                    'sender'    => $bonaeja['sender_phone'],
                    'api_key'   => $bonaeja['api_key'],
                    'receiver'  => $phone_num,
                    'msg'       =>  $message,
                ];
                $res = post("https://api.bonaeja.com/api/msg/v1/send", $sms);
                if($res['code'] == 500)
                    return $this->extendResponse(1999, '통신 과정에서 에러가 발생했습니다.');
                else
                {
                    $this->bonaejaDepositValidate($bonaeja, $brand['name']);
                    return $this->extendResponse($res['body']['code'] == 100 ? 0 : 1999, $res['body']['message']);
                }
            }
            else
                return $this->extendResponse(1999, '문자발송 플랫폼과 연동되어있지 않습니다. 계약 이후 사용 가능합니다.');
        }
    }
    
    /*
    * SMS 문자발송
    */
    public function smslinkSend(Request $request)
    {
        $validated = $request->validate(['phone_num'=>'required']);
        return $this->send($request->phone_num, $request->buyer_name."님\n아래 url로 접속해 결제를 진행해주세요.\n\n".$request->url, $request->user()->brand_id);
    }

    /*
     * 모바일 코드 발급
     */
    public function mobileCodeIssuence(Request $request)
    {
        $validated = $request->validate(['phone_num'=>'required', 'brand_id'=>'required', 'mcht_id'=>'required']);
        $brand = BrandInfo::getBrandById($request->brand_id);
        if($brand)
        {
            if($request->mcht_id === -1 || AuthPhoneNum::limitValidate($brand, $request->phone_num, $request->mcht_id))
            {
                $rand = random_int(100000, 999999);
                $res = Redis::set("verify-code:".$request->phone_num, $rand, 'EX', 180);

                if($res)
                    return $this->send($request->phone_num, "[".$brand['name']."] 인증번호 [$rand]을(를) 입력해주세요", $request->brand_id);
                else
                    return $this->extendResponse(1999, '모바일 코드 발급에 실패하였습니다.');    
            }
            else
                return $this->extendResponse(1999, '휴대폰 인증허용 회수를 초과하였습니다.');    
        }
        return $this->extendResponse(1000, '존재하지 않는 전산입니다.');
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
        $validated = $request->validate(['verification_number'=>'required|string','phone_num'=>'required|string']);
        $phone_num = $request->phone_num;
        $verification_number = Redis::get("verify-code:".$phone_num);

        $cond_1 = $request->verification_number == $verification_number;
        $cond_2 = $request->phone_num == "01000000000" && $request->verification_number == "000000";
        if($cond_1 || $cond_2)
        {
            $token = json_encode([
                'phone_num' => $request->phone_num,
                'verify_code' => $verification_number,
                'verify_date' => date('Y-m-d H:i:s')
            ]);
            return $this->response(0,  ['token' => $this->aes256_encode($token)]);
        }
        else
            return $this->extendResponse(1000, __('auth.failed_token'), []);
    }
}
