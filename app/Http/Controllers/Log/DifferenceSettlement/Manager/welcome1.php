<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class welcome1 implements DifferenceSettlementInterface
{
    use FileRWTrait;
    public $mcht_cards = [
        '06' => '국민',
        '16' => '농협',
        '11' => '비씨',
        '04' => '현대',
        '14' => '신한',
        '01' => '하나',
        '12' => '삼성',
        '03' => '롯데',
        '44' => '우리',
    ];

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
    
                $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2);
                $appr_type      = $this->setAtypeField($appr_type, 1);
                $trx_dt         = $this->setNtypeField($trx_dt, 8);
                $brand_business_num = $this->setAtypeField($brand_business_num, 10);
                $business_num   = $this->setAtypeField($business_num, 10);
                $trx_id         = $this->setAtypeField($trx_id, 40);
                $ori_trx_id     = $this->setAtypeField($trans[$i]->trx_id, 40);
                $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 64);
                $amount         = $this->setNtypeField($amount, 15);
                $ori_amount     = $this->setNtypeField($amount, 15);
                $ori_trx_dt     = $this->setNtypeField($ori_trx_dt, 8);
                $add_field      = $this->setAtypeField($trans[$i]->id, 40);
                $filter         = $this->setAtypeField('', 97);
    
                $data_record = 
                    $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.
                    $trx_id.$ori_trx_id.$ord_num.$amount.$ori_amount.$ori_trx_dt.
                    $add_field.$filter;

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
            return substr($line, 0, 2) === DifferenceSettleHectoRecordType::DATA->value;
        }));
        for ($i=0; $i < count($datas); $i++) 
        {
            $data = $datas[$i];
            $is_cancel  = $this->getNtypeField($data, 2, 1);
            $req_dt     = $this->getNtypeField($data, 3, 8);
            $add_field  = $this->getAtypeField($data, 213, 40);
            $mcht_section_code = $this->getAtypeField($data, 253, 1);
            $supply_amount  = $this->getNtypeField($data, 256, 15);
            $vat_amount     = $this->getNtypeField($data, 271, 15);
            $settle_amount  = $supply_amount + $vat_amount;
            $settle_dt = $this->getNtypeField($data, 286, 8);
            $settle_result_code = $this->getAtypeField($data, 294, 2);
            // 정산금이 존재할 때만
            if($supply_amount > 0)
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
            '00' => '정상',
            '01' => '카드사별 구분값 오류(미존재 또는 불일치)',
            '02' => '매출금액 오류(원매출금액과 하위사업자 매출액 SUM의 불일치)',
            '03' => '중복접수(기 처리된 내역을 전송)',
            '04' => '원매입 반송(원매출 미존재 또는 매출금액 오류 등)',
            '05' => '매입취소구분 오류(원매출과 하위매출의 정상/취소 불일치)',
            '06' => '매입전송일자 오류',
            '07' => '승인일자 오류',
            '08' => '승인번호 오류(원승인번호에 해당하는 매출 미존재)',
            '09' => '가맹점번호 오류1(가맹점번호가 SPACE이거나 미등록가맹점)',
            '10' => '가맹점번호 오류2(차액정산 가맹점 번호가 아님)',
            '11' => '카드번호 오류',
            '12' => '중간하위사업자 오류(전자금융업자 미해당사업자 등)',
            '13' => '차액정산 지연접수',
            '14' => '카드사별 구분값 정상건 반송 (A,B,C로 구성된 장바구니 거래에서 C의 카드사별 구분값이 오류인 경우 C는 01번 코드로 회신하나, A,B는 카드사별 구분값이 정상임에도 C로 인해 반송되는 것이므로 14번 코드로 구별하여 회신)',
            '15' => '매출이 전체 취소된 이후, +차액정산 접수시 반송(반송조건 추가 사유 : -차액정산(취소) 접수가 지연 되는 케이스 막고자 함)',
            '98' => '롯데카드 기타 오류 중 기 승인/취소 완료된 거래',
            '99' => '기타',
        ];
        return isset($card_compnay_codes[$code]) ? $card_compnay_codes[$code] : '알수없는 코드';
    }

    public function registerRequest($brand, $req_date, $mchts, $sub_business_regi_infos)
    {
        $getHeader = function($brand, $req_date) {
            return 
                $this->setAtypeField(DifferenceSettleHectoRecordType::HEADER->value, 2).
                $this->setNtypeField($req_date, 8).
                $this->setAtypeField('', 490).
                "\r\n";
        };
        $getDatas = function($brand, $mchts, $sub_business_regi_infos) {
            $yesterday = Carbon::now()->subDay(1)->format('Ymd');
            $records = '';
            for ($i=0; $i < count($sub_business_regi_infos); $i++) 
            { 
                $sub_business_regi_info = $sub_business_regi_infos[$i];
                $mcht = $mchts->first(function ($mcht) use ($sub_business_regi_info) {
                    return $mcht->business_num === $sub_business_regi_info->business_num;
                });
                if($mcht)
                {
                    $records .= $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2)
                        .$this->setNtypeField($sub_business_regi_info->registration_type, 2)
                        .$this->setNtypeField(str_replace('-', '', $brand['business_num']), 10)
                        .$this->setNtypeField($sub_business_regi_info->card_company_code, 2)
                        .$this->setNtypeField(str_replace('-', '', $mchts[$i]->business_num), 10)
                        .$this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->sector), 20)
                        .$this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->mcht_name), 40)
                        .$this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->website_url), 80)   //웹사이트 URL 필드
                        .$this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->addr), 100)
                        .$this->setNtypeField('00000', 6)   //우편번호
                        .$this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mchts[$i]->nick_name), 40)
                        .$this->setNtypeField(str_replace('-', '', $mchts[$i]->phone_num), 11)
                        .$this->setAtypeField(iconv('UTF-8', 'EUC-KR//IGNORE', $mcht->email), 40)   //이메일 필드
                        .$this->setNtypeField($yesterday, 8)
                        .$this->setAtypeField($mchts[$i]->id, 20)
                        .$this->setAtypeField('', 109)
                        ."\r\n";
                }
            }
            return $records;
        };
        $getTrailer = function($mchts) {
            return 
                $this->setAtypeField("30", 2).
                $this->setNtypeField(count($mchts), 10).
                $this->setAtypeField('', 488).
                "\r\n";
        };
        $records  = $getHeader($brand, $req_date);
        $records .= $getDatas($brand, $mchts);
        $records .= $getTrailer($mchts);
        return $records;
    }
}
