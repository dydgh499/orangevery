<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementBase;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class hecto extends DifferenceSettlementBase implements DifferenceSettlementInterface
{
    use FileRWTrait;
    public $mcht_cards = [
        '1001' => '비씨카드',
        '1002' => '국민카드',
        '1003' => '하나(외환)카드',
        '1004' => '삼성카드',
        '1005' => '신한카드',
        '1007' => '우리카드',
        '1008' => '현대카드',
        '1009' => '롯데카드',
        '1015' => 'NH카드',
    ];

    public function setDataRecord($trans, $brand_business_num, $mid)
    {
        $brand_business_num = str_replace('-', '', $brand_business_num);
        $data_histories = [];
        $data_records   = '';
        $total_amount   = 0;
        $total_count    = 0;
        for ($i=0; $i < count($trans); $i++) 
        {
            $business_num = str_replace('-', '', $trans[$i]->business_num);
            if($business_num)
            {
                $appr_type  = $trans[$i]->is_cancel ? "1" : "0";
                $trx_dt     = $trans[$i]->is_cancel ? $trans[$i]->cxl_dt : $trans[$i]->trx_dt;
                $trx_id     = $trans[$i]->is_cancel ? $trans[$i]->ori_trx_id : $trans[$i]->trx_id;
                $trx_dt     = date('Ymd', strtotime($trx_dt));
                $ori_trx_dt = $trans[$i]->trx_dt;
                $ori_trx_dt = date('Ymd', strtotime($ori_trx_dt));
                // 부분취소 차수 (승인:0, N회차: N)
                $part_cxl_type = $trans[$i]->is_cancel ? $trans[$i]->cxl_seq : '0';
                $amount = abs($trans[$i]->amount);

                $total_amount   += $trans[$i]->amount;

                $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2);
                $appr_type      = $this->setAtypeField($appr_type, 1);
                $trx_dt         = $this->setNtypeField($trx_dt, 8);
                $brand_business_num = $this->setAtypeField($brand_business_num, 10);
                $business_num   = $this->setAtypeField($business_num, 10);
                $trx_id         = $this->setAtypeField($trx_id, 40);
                $installment    = $this->setNtypeField($part_cxl_type, 2);
                $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 100);
                $amount         = $this->setNtypeField($amount, 15);
                $ori_amount     = $this->setNtypeField($amount, 15);
                $add_field      = $this->setAtypeField($trans[$i]->id, 40);
                $filter         = $this->setAtypeField('', 149);

                $data_record = 
                    $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.
                    $trx_id.$installment.$ord_num.$amount.$ori_amount.$ori_trx_dt.
                    $add_field.$filter;
                $data_records .= $data_record."\r\n";
                $total_count += 1;
                array_push($data_histories, $this->getSettlementHistoryObejct($trans[$i]->id));
            }
            else
                array_push($data_histories, $this->getSettlementHistoryObejct($trans[$i]->id, '-100'));
        }
        return [$data_records, $total_count, $total_amount, $data_histories];
    }

    public function getDataRecord($contents)
    {
        $records = [];
        $cur_date = date('Y-m-d H:i:s');
        $lines = explode("\n", $contents);
        $datas = array_values(array_filter($lines, function($line) {
            return substr($line, 0, 2) === DifferenceSettleHectoRecordType::DATA->value;
        }));
        for ($i=0; $i < count($datas); $i++) 
        { 
            $data = $datas[$i];
            $is_cancel  = $this->getNtypeField($data, 2, 1);    //원래는 A타입으로 읽어야함 내부 로직상 변경
            $add_field  = (int)$this->getAtypeField($data, 219, 40);
            $mcht_section_code = $this->getAtypeField($data, 259, 1);
            $supply_amount  = $this->getNtypeField($data, 260, 15);
            $vat_amount     = $this->getNtypeField($data, 275, 15);
            $settle_amount  = $this->getNtypeField($data, 290, 15);
            $settle_dt = $this->getNtypeField($data, 305, 8);
            $settle_result_code = $this->getAtypeField($data, 313, 4);
            $card_company_result_code = $this->getAtypeField($data, 317, 2);
            // 정산금이 존재할 때만
            if($is_cancel)
            {
                $supply_amount *= -1;
                $vat_amount *= -1;
                $settle_amount *= -1;
            }
            
            $record = $this->getSettlementResponseObejct($add_field, $settle_result_code, $this->getSettleMessage($settle_result_code), $mcht_section_code, $cur_date);
            if($settle_result_code === '0000')
            {
                $record = array_merge(
                    $record,
                    $this->getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
                );
            }
            $records = $this->setSettlementResponseList($records, $record, 'hecto');
        }
        return $records;
    }

    private function getSettleMessage($code)
    {
        $settle_codes = [
            '0000' => '정상',
            '1101' => '상점 ID 미 존재',
            '1102' => '차액 정산 비대상 가맹점',
            '1103' => '영중소 사업자번호 아님', 
            '1201' => '해당거래 미 존재', 
            '1202' => '원거래 매입금액 불일치', 
            '1203' => '원거래 매입금액과 하위사업자 매출액 불일치', 
            '1301' => '매입전 취소 거래', 
            '1302' => '당일 취소 거래', 
            '1401' => '차액 정산 매입결과 미수신', 
            '9999' => '기타오류',
        ];
        return isset($settle_codes[$code]) ? $settle_codes[$code] : '알수없는 코드';
    }

    public function registerRequest($brand, $req_date, $mchts, $sub_business_regi_infos)
    {
        $getHeader = function($brand, $req_date) {
            return 
                $this->setAtypeField("HD", 2).
                $this->setNtypeField($req_date, 8).
                $this->setAtypeField('', 490).
                "\r\n";
        };
        $getDatas = function($brand, $mchts, $sub_business_regi_infos) {
            $full_records = '';
            $upload = [
                'new_count' => 0, 
                'remove_count' => 0, 
                'modify_count' => 0,
                'total_count' => 0,
            ];
            $yesterday = Carbon::now()->subDay(1)->format('Ymd');
            for ($i=0; $i < count($sub_business_regi_infos); $i++) 
            { 
                $sub_business_regi_info = $sub_business_regi_infos[$i];
                $mcht = $mchts->first(function ($mcht) use ($sub_business_regi_info) {
                    return $mcht->business_num === $sub_business_regi_info->business_num;
                });
                if($mcht)
                {
                    $records = $this->setAtypeField("RD", 2);
                    $records .= $this->setAtypeField($sub_business_regi_info->registration_type, 2);
                    $records .= $this->setAtypeField($brand['rep_mid'], 10);
                    $records .= $this->setAtypeField($sub_business_regi_info->card_company_code, 3);
                    $records .= $this->setAtypeField(str_replace('-', '', $mcht->business_num), 10);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->sector), 20);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->mcht_name), 40);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->addr), 100);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->nick_name), 40);
                    $records .= $this->setAtypeField(str_replace('-', '', $mcht->phone_num), 11);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->email), 40);   //이메일 필드
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->website_url), 80);   //웹사이트 URL 필드
                    $records .= $this->setNtypeField($yesterday, 8);
                    $records .= $this->setAtypeField('', 128);
                    $records .= "\r\n";      
                    $full_records .= $records;
                    
                    if($sub_business_regi_info->registration_type === 0)
                        $upload['new_count']++;
                    else if($sub_business_regi_info->registration_type === 1)
                        $upload['remove_count']++;
                    else if($sub_business_regi_info->registration_type === 2)
                        $upload['modify_count']++;
                    $upload['total_count']++;
                }
            }
            return [$full_records, $upload];
        };
        $getTrailer = function($upload) {
            return 
                $this->setAtypeField("TR", 2).
                $this->setNtypeField($upload['total_count'], 10).
                $this->setNtypeField($upload['new_count'], 10).
                $this->setNtypeField($upload['remove_count'], 10).
                $this->setNtypeField($upload['modify_count'], 10).
                $this->setAtypeField('', 458).
                "\r\n";
        };
        [$datas, $upload] = $getDatas($brand, $mchts, $sub_business_regi_infos);

        $records  = $getHeader($brand, $req_date);
        $records .= $datas;
        $records .= $getTrailer($upload);
        return $records;
    }

    public function registerResponse($content)
    {
        $pgResultMessage = function($code) {
            $result_codes = [
                '00' => '정상처리',
                '01' => '미정의 등록구분 값',
                '02' => '미정의 카드사코드 값',
                '03' => '필수 값 누락',
                '04' => '중복사업자번호(하위몰)',
            ];
            return isset($result_codes[$code]) ? $result_codes[$code] : '알수없는 코드';
        };
        $records = [];
        $lines = explode("\n", $content);
        $datas = array_values(array_filter($lines, function($line) {
            return substr($line, 0, 2) === 'RD';
        }));
        for ($i=0; $i < count($datas); $i++) 
        { 
            /*
                $data = iconv('EUC-KR', 'UTF-8', $datas[$i]);
                $add_field      = $this->getNtypeField($data, 372, 12);
                $pg_result_code = $this->getNtypeField($data, 371, 2);  
                $pg_result_msg  = $pgResultMessage($pg_result_code);
                $record = [
                    'id' => (int)$add_field,
                    'registration_result' => (int)$pg_result_code,
                    'registration_msg' => $pg_result_msg,
                ];
            */
        }
        return $records;
    }
}
