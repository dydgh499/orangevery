<?php
namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Utils\Comm;
use App\Models\Brand;
use Illuminate\Support\Facades\Redis;

class SMS
{
    /*
     * 출금가능 잔액 조회
     */
    static public function getSendAbleAmount($brand_id)
    {
        $brand = BrandInfo::getBrandById($brand_id);
        if($brand)
        {
            $bonaeja = $brand['ov_options']['free']['bonaeja'];
            $params = [
                'user_id'   => $bonaeja['user_id'],
                'api_key'   => $bonaeja['api_key'],
            ];
            $res = Comm::post("https://api.bonaeja.com/api/msg/v1/remain", $params);
            if($res['body']['code'] === 100)
            {
                $total_deposit = $res['body']['data']['TOTAL_DEPOSIT'];
                return [true, $total_deposit];
            }
        }
        return [false, 0];
    }

    /*
     * 예치금 잔액 검증
     */
    static public function validate($brand_id)
    {
        $brand = BrandInfo::getBrandById($brand_id);
        if($brand)
        {
            $bonaeja = $brand['ov_options']['free']['bonaeja'];
            [$result, $send_able_amount] = self::getSendAbleAmount($brand_id);
            if($result)
            {
                if($send_able_amount < ((int)$bonaeja['min_balance_limit'] * 10000))
                {
                    $message = "[".$brand['name']."] 보내자 예치금이 부족합니다. 예치금을 충전해주세요.(현재 잔액:".number_format($send_able_amount)."원)";
                    self::send($bonaeja['receive_phone'], $message, $brand_id);
                }
            }
        }
    }

    static public function send($phone_num, $message, $brand_id)
    {
        $brand = BrandInfo::getBrandById($brand_id);
        if($brand)
        {
            $bonaeja = $brand['ov_options']['free']['bonaeja'];
            if($bonaeja['user_id'] && $bonaeja['api_key'] && $bonaeja['sender_phone'])
            {
                $phone_num = str_replace("-", '', $phone_num);
                $phone_num = str_replace(" ", '', $phone_num);
                $sms = [
                    'user_id'   => $bonaeja['user_id'],
                    'sender'    => $bonaeja['sender_phone'],
                    'api_key'   => $bonaeja['api_key'],
                    'receiver'  => $phone_num,
                    'msg'       => $message,
                ];
                $res = Comm::post("https://api.bonaeja.com/api/msg/v1/send", $sms);
                if($res['body']['code'] !== 100)
                    Log::warning("bonaeja response error", $res);
                return $res;
            }
        }
        return null;
    }
}
