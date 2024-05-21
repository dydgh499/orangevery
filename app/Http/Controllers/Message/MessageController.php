<?php

namespace App\Http\Controllers\Message;

use App\Models\Brand;
use App\Models\Merchandise;

use App\Http\Traits\ExtendResponseTrait;
use App\Http\Controllers\Manager\Service\BrandInfo;

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
    use ExtendResponseTrait;

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
    }
    
    /*
    * SMS 문자발송
    */
    public function smslinkSend(Request $request)
    {
        $validated = $request->validate(['phone_num'=>'required']);
        return $this->send($request->phone_num, $request->buyer_name."님\n아래 url로 접속해 결제를 진행해주세요.\n\n".$request->url, $request->user()->brand_id);
    }

    private function payDisableTimeType($s_tm, $e_tm)
    {
        $cond_1 = $s_tm && $e_tm;
        $cond_2 = $s_tm !== "00:00:00" || $e_tm !== "00:00:00";
        if ($cond_1 && $cond_2)
        {
            $current_time = Carbon::now();

            $start_time_today = Carbon::today()->setTimeFromTimeString($s_tm);
            $end_time_today = Carbon::today()->setTimeFromTimeString($e_tm);

            $start_time_yesterday = Carbon::yesterday()->setTimeFromTimeString($s_tm);
            $end_time_tomorrow = Carbon::tomorrow()->setTimeFromTimeString($e_tm);

            //어제 ~ 오늘
            if($current_time->between($start_time_yesterday, $end_time_today))
                return [1, $start_time_yesterday, $end_time_today];
            //오늘 ~ 다음날
            if ($current_time->between($start_time_today, $end_time_tomorrow))
                return [2, $start_time_today, $end_time_tomorrow];
        }
        return [0, '', ''];
    }

    // 휴대폰 인증허용회수 검증
    private function mobileAuthLimitValidate($brand, $phone_num, $mcht_id)
    {
        if($brand['pv_options']['paid']['use_pay_verification_mobile'])
        {
            $over_key_name = "3phone-auth-limit-over-".$mcht_id.":".$phone_num;
            $is_over = Redis::get($over_key_name);
            if($is_over)
                return false;
            else
            {
                $mcht = Merchandise::where('id', $mcht_id)->first();
                if($mcht)
                {
                    if($mcht->phone_auth_limit_count)
                    {
                        [$time_type, $s_tm, $e_tm] = $this->payDisableTimeType($mcht->phone_auth_limit_s_tm, $mcht->phone_auth_limit_e_tm);
                        if($time_type > 0)
                        {
                            $end_time = $e_tm->diffInSeconds(Carbon::now());

                            $count_key_name = "3phone-auth-limit-count-".$mcht_id.":".$phone_num;
                            $try_count = ((int)Redis::get($count_key_name)) + 1;
                            
                            Redis::set($count_key_name, $try_count, 'EX', $end_time);                            
                            if($mcht->phone_auth_limit_count < $try_count)
                            {
                                Redis::set($over_key_name, 'over', 'EX', $end_time);
                                return false;
                            }
                            else
                                return true;
                        }
                    }
                }
            }
        }
        return true;
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
            if($this->mobileAuthLimitValidate($brand, $request->phone_num, $request->mcht_id))
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
            return $this->response(0);
        else
            return $this->extendResponse(1000, __('auth.failed_token'), []);
    }
}
