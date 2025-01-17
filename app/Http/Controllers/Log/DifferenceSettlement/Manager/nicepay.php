<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementBase;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class nicepay extends DifferenceSettlementBase implements DifferenceSettlementInterface
{
    use FileRWTrait;
    public $mcht_cards = [
        '016' => '국민',
        '018' => '농협',
        '026' => '비씨',
        '027' => '현대',
        '029' => '신한',
        '006' => '하나',
        '047' => '롯데',
        '031' => '삼성',
    ];

    public function setDataRecord($trans, $brand_business_num, $mid)
    {
        $brand_business_num = trim(str_replace('-', '', $brand_business_num));
        $data_histories = [];
        $data_records   = '';
        $total_amount   = 0;
        $total_count    = 0;
        for ($i=0; $i < count($trans); $i++) 
        { 
            $business_num = trim(str_replace('-', '', $trans[$i]->business_num));
            if($business_num)
            {
                $total_amount += $trans[$i]->amount;
                $total_count += 1;
                $records = 
                    $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2).
                    $this->setNtypeField($trans[$i]->is_cancel ? "1" : "0", 1).
                    $this->setNtypeField(Carbon::createFromFormat('Y-m-d H:i:s', $trans[$i]->trx_at)->format('Ymd'), 8).
                    $this->setAtypeField($brand_business_num, 10).
                    $this->setAtypeField($business_num, 10).
                    $this->setAtypeField($trans[$i]->trx_id, 30).
                    $this->setNtypeField($trans[$i]->is_cancel ? $trans[$i]->ori_trx_id : $trans[$i]->trx_id, 30).
                    $this->setAtypeField($trans[$i]->ord_num, 70).
                    $this->setNtypeField(abs($trans[$i]->amount), 15).
                    $this->setNtypeField(abs($trans[$i]->amount), 15).
                    $this->setNtypeField(Carbon::createFromFormat('Y-m-d', $trans[$i]->trx_dt)->format('Ymd'), 8).
                    $this->setAtypeField($trans[$i]->id, 40).
                    $this->setAtypeField('', 161).
                    "\r\n";
    
                $data_records .= $records;
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
            $req_dt     = $this->getNtypeField($data, 3, 8);
            $add_field  = (int)$this->getAtypeField($data, 199, 40);
            $mcht_section_code = $this->getAtypeField($data, 239, 1);
            $supply_amount  = $this->getNtypeField($data, 242, 15);
            $vat_amount     = $this->getNtypeField($data, 257, 15);
            $settle_amount  = $this->getNtypeField($data, 272, 15);
            $settle_dt = $this->getNtypeField($data, 287, 8);
            $settle_result_code = $this->getAtypeField($data, 295, 2);

            $record = $this->getSettlementResponseObejct($add_field, $settle_result_code, $this->getSettleMessage($settle_result_code), $mcht_section_code, $cur_date);
            if($settle_result_code === '00')
            {
                $record = array_merge(
                    $record,
                    $this->getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
                );
            }
            $records = $this->setSettlementResponseList($records, $record, 'nicepay');
        }
        return $records;
    }

    public function getSettleMessage($code)
    {
        $settle_codes = [
            '00' => '정상',
            '01' => '매출정보 오류',
            '02' => '정보 오류',    // (사업자번호, 가맹점번호 불일치 등)
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
            $seq = 1;
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
                    if(substr_count($mcht->phone_num, '-') === 2)
                    {
                        $nums = explode('-', $mcht->phone_num);
                        $region_num = $nums[0];
                        $number_1 = $nums[1];
                        $number_2 = $nums[2];
                    }
                    else if(strlen($mcht->phone_num) === 9)
                    {
                        $region_num = substr($mcht->phone_num, 0, 2);
                        $number_1 = substr($mcht->phone_num, 2, 4);
                        $number_2 = substr($mcht->phone_num, 6, 4);
                    }
                    else if(strlen($mcht->phone_num) === 10)
                    {
                        $region_num = substr($mcht->phone_num, 0, 3);
                        $number_1 = substr($mcht->phone_num, 3, 4);
                        $number_2 = substr($mcht->phone_num, 7, 4);
                    }
                    else
                    {
                        $region_num = '02';
                        $number_1 = '1234';
                        $number_2 = '5678';
                    }
                    $records = $this->setAtypeField("RD", 2);
                    $records .= $this->setAtypeField($sub_business_regi_info->registration_type, 2);
                    $records .= $this->setAtypeField($brand['business_num'], 10);
                    $records .= $this->setAtypeField($sub_business_regi_info->card_company_code, 3);
                    $records .= $this->setAtypeField(str_replace('-', '', $mcht->business_num), 10);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->sector), 20);  // 업종명(하위몰)
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->mcht_name), 40);
                    $records .= $this->setNtypeField('000000', 6); // 우편번호(하위몰)
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->addr), 100);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->nick_name), 40);   // 대표자명(하위몰)
                    
                    $records .= $this->setNtypeField($region_num, 3);  //지역번호 필드
                    $records .= $this->setNtypeField($number_1, 4);   //국번번호 필드
                    $records .= $this->setNtypeField($number_2, 4);   //개별번호 필드

                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->email), 40);   //이메일 필드
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->website_url), 80);   //웹사이트 URL 필드
                    $records .= $this->setNtypeField($yesterday, 8);
                    $records .= $this->setNtypeField($seq, 12);
                    $records .= $this->setAtypeField('', 116);
                    $records .= "\r\n";      
                    $full_records .= $records;
                    
                    if($sub_business_regi_info->registration_type === 0)
                        $upload['new_count']++;
                    else if($sub_business_regi_info->registration_type === 1)
                        $upload['remove_count']++;
                    else if($sub_business_regi_info->registration_type === 2)
                        $upload['modify_count']++;
                    $upload['total_count']++;
                    $seq++;
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
