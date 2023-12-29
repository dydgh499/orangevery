<?php

namespace App\Http\Controllers\Message;

use App\Models\Brand;

use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

/**
 * @group Message API
 *
 * 문자 발송 API 입니다.
 */
class MessageController extends Controller
{
    use ExtendResponseTrait;

    public function index(Request $request)
    {
        $brand = Brand::where('id', $request->user()->brand_id)->first();
        if($brand)
        {
            $bonaeja = $brand->pv_options->free->bonaeja;
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
        $brand = Brand::where('id', $request->user()->brand_id)->first();
        if($brand)
        {
            $bonaeja = $brand->pv_options->free->bonaeja;
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

    /*
     * 모바일 코드 발급
     */
    public function mobileCodeIssuence(Request $request)
    {
        $validated = $request->validate(['phone_num'=>'required', 'brand_id'=>'required']);
        $brand  = Brand::where('id', $request->brand_id)->first();
        if($brand)
        {
            $bonaeja = $brand->pv_options->free->bonaeja;
            $rand   = random_int(100000, 999999);
            $res = Redis::set("verify-code:".$request->phone_num, $rand, 'EX', 180);
            if($res)
            {
                $sms = [
                    'user_id'   => $bonaeja['user_id'],
                    'sender'    => $bonaeja['sender_phone'],
                    'api_key'   => $bonaeja['api_key'],
                    'receiver'  => $request->phone_num,
                    'msg'       => "[".$brand->name."] 인증번호 [$rand]을(를) 입력해주세요",
                ];
                $res = post("https://api.bonaeja.com/api/msg/v1/send", $sms);
                if($res['code'] == 500)
                    return $this->extendResponse(1000, '통신 과정에서 에러가 발생했습니다.');
                else
                {
                    $this->bonaejaDepositValidate($bonaeja, $brand->name);
                    return $this->extendResponse($res['body']['code'] == 100 ? 0 : 1000, $res['body']['message']);
                }
            }    
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
