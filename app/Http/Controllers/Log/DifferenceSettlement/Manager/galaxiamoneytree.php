<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementBase;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class galaxiamoneytree extends DifferenceSettlementBase implements DifferenceSettlementInterface
{
    use FileRWTrait;
    public $mcht_cards = [
        '050' => '국민',
        '078' => '농협',
        '052' => '비씨',
        '073' => '현대',
        '053' => '신한',
        '051' => '하나',
        '054' => '삼성',
        '055' => '롯데',
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
                $appr_type  = $trans[$i]->is_cancel ? "1" : "0";
                $trx_dt     = $trans[$i]->is_cancel ? $trans[$i]->cxl_dt : $trans[$i]->trx_dt;
                $trx_dt     = date('Ymd', strtotime($trx_dt));
                $ori_trx_dt = $trans[$i]->trx_dt;
                $ori_trx_dt = date('Ymd', strtotime($ori_trx_dt));
                // amount
                $total_amount += abs($trans[$i]->amount);   //갤럭시아는 취소도 -로 계산안함
                $amount = abs($trans[$i]->amount);
    
                $record_type    = $this->setAtypeField('DT', 2);
                $appr_type      = $this->setAtypeField($appr_type, 1);
                $trx_dt         = $this->setNtypeField($trx_dt, 8);
                $brand_business_num = $this->setAtypeField($brand_business_num, 10);
                $business_num   = $this->setAtypeField($business_num, 10);
                $trx_id         = $this->setAtypeField($trans[$i]->trx_id, 20);
                $part_cxl_type  = $this->setNtypeField($trans[$i]->is_cancel ? ($trans[$i]->cxl_seq + 1) : 1, 2);  //trx seq
                $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 64);
                $amount         = $this->setNtypeField($amount, 15);
                $ori_amount     = $this->setNtypeField($amount, 15);
                $add_field      = $this->setAtypeField($trans[$i]->id, 30);

                $data_record = 
                    $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.
                    $trx_id.$part_cxl_type.$ord_num.$amount.$ori_amount.$add_field;

                $data_records .= $data_record."\n";
                $total_count += 1;
                array_push($data_histories, $this->getSettlementHistoryObejct($trans[$i]->id));
            }
            else
                array_push($data_histories, $this->getSettlementHistoryObejct($trans[$i]->id, '-100'));
        }
        return [$data_records, $total_count, $total_amount];
    }

    public function getDataRecord($contents)
    {
        $records = [];
        $cur_date = date('Y-m-d H:i:s');
        $lines = explode("\n", $contents);
        $datas = array_values(array_filter($lines, function($line) {
            return substr($line, 0, 2) === 'DT';
        }));
        for ($i=0; $i < count($datas); $i++) 
        { 
            $data = $datas[$i];
            $is_cancel  = $this->getNtypeField($data, 2, 1);    //원래는 A타입으로 읽어야함 내부 로직상 변경
            $req_dt     = $this->getNtypeField($data, 3, 8);
            $add_field  = $this->getAtypeField($data, 147, 30);
            $mcht_section_code = $this->getAtypeField($data, 177, 1);
            $settle_amount  = $this->getNtypeField($data, 178, 15);

            $supply_amount  = round((int)$settle_amount/1.1);
            $vat_amount     = (int)$settle_amount - $supply_amount;

            $settle_dt = date('Ymd');
            $settle_result_code = $this->getAtypeField($data, 193, 2);

            $record = $this->getSettlementResponseObejct($add_field, $settle_result_code, $this->getSettleMessage($settle_result_code), $mcht_section_code, $cur_date);
            if($settle_result_code === '00')
            {
                $record = array_merge(
                    $record,
                    $this->getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
                );
            }
            $records = $this->setSettlementResponseList($records, $record, 'galaxiamoneytree');
        }
        return $records;
    }

    public function getSettleMessage($code)
    {
        $settle_codes = [
            '00' => '정상처리',
            '01' => '승인취소 거래건',
            '02' => '매출금액 오류',
            '03' => '중복 접수',
            '04' => '원거래 매입 거절건',
            '05' => '매입취소구분 오류',
            '06' => '일반사업자',
            '07' => '차액정산 지연접수',
            '08' => '원거래 없음',
            '09' => '차액정산 대상아님',
            '99' => '기타',
        ];
        return isset($settle_codes[$code]) ? $settle_codes[$code] : '알수없는 코드';
    }

    public function registerRequest($brand, $req_date, $mchts, $sub_business_regi_infos)
    {
        $getHeader = function($brand, $req_date) {
            return 
                $this->setAtypeField("HD", 2).
                $this->setNtypeField($req_date, 8).
                $this->setAtypeField('', 490)
                ."\r\n";
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
                    $records .= $this->setNtypeField($sub_business_regi_info->registration_type, 2);
                    $records .= $this->setNtypeField(str_replace('-', '', $brand['business_num']), 10);
                    $records .= $this->setNtypeField($sub_business_regi_info->card_company_code, 3);
                    $records .= $this->setNtypeField(str_replace('-', '', $mcht->business_num), 10);

                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->sector), 20);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->mcht_name), 40);
                    $records .= $this->setNtypeField('00000', 6);   //우편번호
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->addr), 100);
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->nick_name), 40);
                    $records .= $this->setNtypeField($region_num, 3);  //지역번호 필드
                    $records .= $this->setNtypeField($number_1, 4);   //국번번호 필드
                    $records .= $this->setNtypeField($number_2, 4);   //개별번호 필드
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->email), 40);   //이메일 필드
                    $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->website_url), 80);   //웹사이트 URL 필드
                    $records .= $this->setNtypeField($yesterday, 8);
                    $records .= $this->setAtypeField($mcht->id, 12);
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

    public function registerResponse($content, $sub_business_regi_infos)
    {
        $records = [];
        $cur_date = date('Y-m-d H:i:s');
        $lines = explode("\n", $contents);
        $datas = array_values(array_filter($lines, function($line) {
            return substr($line, 0, 2) === 'DD';
        }));
        for ($i=0; $i < count($datas); $i++) 
        { 
            $data = $datas[$i];
        }
        return $records;
    }
}
