<?php

namespace App\Jobs\Realtime\Hecto;
use App\Jobs\Realtime\RealtimeFDS;
use Illuminate\Support\Facades\Log;

class Hecto extends RealtimeFDS
{
    public $is_test, $host, $timeout_codes;
    public function __construct($finance_van, $privacy, $deposit_type, $withdraw_book_time)
    {
        parent::__construct($finance_van, $privacy, $deposit_type, $withdraw_book_time);
        $this->is_test = $finance_van['enc_key'] === "SETTLEBANKISGOODSETTLEBANKISGOOD" ? true : false;
        $this->host     = "https://".($this->is_test ? "tbgw.settlebank.co.kr" : "gw.settlebank.co.kr");
        $this->timeout_codes = ['VTIM'];
    }

    protected function send($url, $params)
    {
        $res = $this->curlPost($this->host.$url, http_build_query($params), []);
        $code = $res['body']['outRsltCd'];
        $message = $res['body']['outRsltMsg'];

        $log = [
            'RESP_CD'   => $code,
            'RESP_MSG'  => $message,
        ];

        if($code !== '0000')
        {
            Log::warning("hecto finance van warning", array_merge([
                'url' => $this->host.$url
            ], $res['body']));
        }

        return array_merge($res['body'], $log);
    }

    protected function getBankCode($bank_name)
    {
        $banks = [
            ['code' => '001', 'name' => '한국은행'],
            ['code' => '002', 'name' => '산업은행'],
            ['code' => '003', 'name' => '기업은행'],
            ['code' => '004', 'name' => '국민은행'],
            ['code' => '005', 'name' => '하나은행'],
            ['code' => '007', 'name' => '수협은행'],    //수협중앙회
            ['code' => '011', 'name' => '농협은행'],
            ['code' => '012', 'name' => '농협중앙회'],  //지역농축협
            ['code' => '020', 'name' => '우리은행'],
            ['code' => '023', 'name' => 'SC제일은행'],
            ['code' => '027', 'name' => '한국씨티은행'],
            ['code' => '031', 'name' => '대구은행'],
            ['code' => '032', 'name' => '부산은행'],
            ['code' => '034', 'name' => '광주은행'],
            ['code' => '035', 'name' => '제주은행'],
            ['code' => '037', 'name' => '전북은행'],
            ['code' => '039', 'name' => '경남은행'],
            ['code' => '045', 'name' => '새마을금고연합회'],
            ['code' => '048', 'name' => '신협중앙회'],
            ['code' => '050', 'name' => '저축은행'],
            ['code' => '052', 'name' => '모건스탠리은행'],
            ['code' => '054', 'name' => 'HSBC은행'],
            ['code' => '055', 'name' => '도이치은행'],
            ['code' => '071', 'name' => '우체국'],
            ['code' => '081', 'name' => 'KEB 하나은행'],
            ['code' => '088', 'name' => '신한은행'],
            ['code' => '089', 'name' => '케이뱅크'],
            ['code' => '090', 'name' => '카카오뱅크'],
            ['code' => '092', 'name' => '토스뱅크'],
        ];
        if(strpos($bank_name, '저축은행') !== false)
            return '050';
        else
        {
            $idx = array_search($bank_name, array_column($banks, 'name'));
            return $idx !== false ? $banks[$idx]['code'] : null;
        }
    }
}

?>
