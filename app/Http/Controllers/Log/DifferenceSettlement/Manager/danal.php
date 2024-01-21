<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Http\Traits\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class danal implements DifferenceSettlementInterface
{
    use FileRWTrait;

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
                $part_cxl_type = $trans[$i]->is_cancel ? '1' : '0';
                // amount
                $total_amount += $trans[$i]->amount;
                $amount = abs($trans[$i]->amount);
    
                $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2);
                $appr_type      = $this->setAtypeField($appr_type, 1);
                $trx_dt         = $this->setNtypeField($trx_dt, 8);
                $brand_business_num = $this->setAtypeField($brand_business_num, 10);
                $business_num   = $this->setAtypeField($business_num, 10);
                $trx_id         = $this->setAtypeField($trx_id, 30);
                $ori_trx_id     = $this->setAtypeField($trans[$i]->trx_id, 30);
                $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 255);
                $amount         = $this->setNtypeField($amount, 15);
                $ori_amount     = $this->setNtypeField($amount, 15);
                $ori_trx_dt     = $this->setNtypeField($ori_trx_dt, 8);

                $installment    = $this->setNtypeField($part_cxl_type, 2);
                $add_field      = $this->setAtypeField($trans[$i]->id, 40);
                $filter         = $this->setAtypeField('', 76);
    
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
            $is_cancel  = $this->getNtypeField($data, 2, 1);    //원래는 A타입으로 읽어야함 내부 로직상 변경
            $req_dt     = $this->getNtypeField($data, 3, 8);
            $add_field  = $this->getAtypeField($data, 384, 40);
            $mcht_section_code = $this->getAtypeField($data, 424, 1);
            $supply_amount  = $this->getNtypeField($data, 427, 15);
            $vat_amount     = $this->getNtypeField($data, 442, 15);
            $settle_amount  = $this->getNtypeField($data, 457, 15);
            $settle_dt = $this->getNtypeField($data, 472, 8);
            $settle_result_code = $this->getAtypeField($data, 480, 2);
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
            '00' => '정상',
            '01' => '매출정보 오류',
            '02' => '정보 오류 (사업자번호, 가맹점번호 불일치 등)',
            '99' => '기타 오류',
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
}
