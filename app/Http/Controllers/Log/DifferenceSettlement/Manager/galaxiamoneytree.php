<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class galaxiamoneytree implements DifferenceSettlementInterface
{
    use FileRWTrait;

    public function setHeaderRecord($rep_mid, $space)
    {
        $record_type    = $this->setAtypeField('HD', 2);
        $create_dt      = $this->setAtypeField(date('Ymd'), 8);
        $rep_mid        = $this->setAtypeField($rep_mid, 20);
        $filter         = $this->setAtypeField('', $space);
        return $record_type.$create_dt.$rep_mid.$filter."\n";    
    }

    public function setTotalRecord($space, $total_count, $total_amount)
    {
        $record_type    = $this->setAtypeField('TR', 2);
        $total_count    = $this->setNtypeField($total_count, 7);
        $total_amount   = $this->setAtypeField($total_amount, 18);
        $filter         = $this->setAtypeField('', $space);
        return $record_type.$total_count.$total_amount.$filter."\n";
    }

    public function setDataRecord($trans, $brand_business_num)
    {
        $brand_business_num = str_replace('-', '', $brand_business_num);
        $data_records = '';
        $total_amount = 0;
        $total_count = 0;
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
                // amount
                $total_amount += $trans[$i]->amount;
                $amount = abs($trans[$i]->amount);
    
                $record_type    = $this->setAtypeField('DT', 2);
                $appr_type      = $this->setAtypeField($appr_type, 1);
                $trx_dt         = $this->setNtypeField($trx_dt, 8);
                $brand_business_num = $this->setAtypeField($brand_business_num, 10);
                $business_num   = $this->setAtypeField($business_num, 10);
                $trx_id         = $this->setAtypeField($trx_id, 20);
                $part_cxl_type  = $this->setNtypeField($part_cxl_type, 2);  //trx seq
                $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 64);
                $amount         = $this->setNtypeField($amount, 15);
                $ori_amount     = $this->setNtypeField($amount, 15);
                $add_field      = $this->setAtypeField($trans[$i]->id, 30);
    
                $data_record = 
                    $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.
                    $trx_id.$part_cxl_type.$ord_num.$amount.$ori_amount.$add_field;

                $data_records .= $data_record."\n";
                $total_count += 1;
            }
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
            // 정산금이 존재할 때만
            if($settle_amount > 0)
            {
                if($is_cancel)
                {
                    $supply_amount *= -1;
                    $vat_amount *= -1;
                    $settle_amount *= -1;
                }
    
                $req_dt     = Carbon::createFromFormat('Ymd', (string)$req_dt)->format('Y-m-d');
                $settle_dt  = Carbon::createFromFormat('Ymd', (string)$settle_dt)->format('Y-m-d');
                if((int)$add_field != 0)
                {
                    $records[] = [
                        'trans_id'   => $add_field,
                        'settle_result_code'    => $settle_result_code,
                        'settle_result_msg'     => $this->getSettleMessage($settle_result_code),
                        'card_company_result_code'  => '',
                        'card_company_result_msg'   => '',
                        'mcht_section_code' => $mcht_section_code,
                        'mcht_section_name'  => $this->getMchtSectionName($mcht_section_code),
                        'req_dt'    => $req_dt,
                        'settle_dt' => $settle_dt,
                        'supply_amount' => $supply_amount,
                        'vat_amount' => $vat_amount,
                        'settle_amount' => $settle_amount,
                        'created_at' => $cur_date,
                        'updated_at' => $cur_date,
                    ];
                }
            }
        }
        return $records;
    }

    private function getSettleMessage($code)
    {
        $card_compnay_codes = [
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
        return isset($card_compnay_codes[$code]) ? $card_compnay_codes[$code] : '알수없는 코드';
    }

    private function getMchtSectionName($code)
    {
        $mcht_sections = [
            '0' => '영세',
            '1' => '중소1',
            '2' => '중소2',
            '3' => '중소3',
            '4' => '일반',
        ];
        return isset($mcht_sections[$code]) ? $mcht_sections[$code] : '알수없는 코드';        
    }

    public function getMchtCardCode($code)
    {
        $mcht_cards = [
            '099' => '전체카드',
            '050' => '국민',
            '078' => '농협',
            '052' => '비씨',
            '073' => '현대',
            '053' => '신한',
            '051' => '하나',
            '054' => '삼성',
            '055' => '롯데',
        ];
        return isset($mcht_cards[$code]) ? $mcht_cards[$code] : '알수없는 코드';   
    }

    public function registerRequest($brand, $req_date, $mchts)
    {
        $getHeader = function($brand, $req_date) {
            return 
                $this->setAtypeField("HD", 2).
                $this->setNtypeField($req_date, 8).
                $this->setAtypeField('', 490);
        };
        $getDatas = function($brand, $mchts) {
            $yesterday = Carbon::now()->subDay(1)->format('Ymd');
            $records = '';
            for ($i=0; $i < count($mchts); $i++) 
            { 
                $records .= $this->setAtypeField("RD", 2);
                $records .= $this->setNtypeField(0, 2);
                $records .= $this->setNtypeField(str_replace('-', '', $brand['business_num']), 10).
                $records .= $this->setNtypeField('000', 3); // $this->getMchtCardCode('카드사 코드???')
                $records .= $this->setNtypeField(str_replace('-', '', $mchts[$i]->business_num), 10);

                $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->sector), 20);
                $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->mcht_name), 40);
                $records .= $this->setNtypeField('', 6);   //우편번호
                $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->addr), 100);
                $records .= $this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->nick_name), 40);
                $records .= $this->setNtypeField($mchts[$i]->phone_num, 11);
                $records .= $this->setNtypeField('', 3);   //지역번호 필드
                $records .= $this->setNtypeField('', 4);   //국번번호 필드
                $records .= $this->setNtypeField('', 4);   //개별번호 필드
                $records .= $this->setAtypeField('', 40);   //이메일 필드
                $records .= $this->setAtypeField('', 80);   //웹사이트 URL 필드
                $records .= $this->setNtypeField($yesterday, 8);
                $records .= $this->setAtypeField($mchts[$i]->id, 12);
                $records .= $this->setAtypeField('', 116);
            }
            return $records;
        };
        $getTrailer = function($mchts) {
            return 
                $this->setAtypeField("TR", 2).
                $this->setNtypeField(count($mchts), 10).
                $this->setNtypeField(count($mchts), 10).
                $this->setNtypeField(0, 10).
                $this->setNtypeField(0, 10).
                $this->setAtypeField('', 458);
        };
        $records  = $getHeader($brand, $req_date)."\n";
        $records .= $getDatas($brand, $mchts)."\n";
        $records .= $getTrailer($mchts)."\n";
        return $records;
    }
}
