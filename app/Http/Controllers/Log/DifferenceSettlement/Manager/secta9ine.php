<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Models\Transaction;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class secta9ine implements DifferenceSettlementInterface
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

    public function setDataRecord($trans, $brand_business_num, $mid)
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
                $trx_dt     = date('ymd', strtotime($trx_dt));
                // 부분취소 차수 (승인:0, N회차: N)
                if($trans[$i]->is_cancel)
                    $record_classification = $trans[$i]->cxl_seq ? 'CP' : 'CC';
                else
                    $record_classification = 'CA';
                // amount
                $total_amount += $trans[$i]->amount;
                $amount = abs($trans[$i]->amount);

                $data_record = 
                    $this->setAtypeField("DT", 2).
                    $this->setAtypeField($appr_type, 1).
                    $trx_dt.
                    $brand_business_num.
                    $business_num.
                    $mid.
                    $this->setAtypeField($trans[$i]->trx_id, 20).
                    $this->setAtypeField($record_classification, 2).
                    $this->setAtypeField($trans[$i]->ord_num, 40).
                    $this->setNtypeField($amount, 15).
                    $this->setNtypeField($amount, 15).
                    $this->setAtypeField($trans[$i]->id, 30).
                    $this->setAtypeField('', 43);

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
            return substr($line, 0, 2) === "DT";
        }));
        for ($i=0; $i < count($datas); $i++) 
        {
            $data = $datas[$i];
            $is_cancel  = $this->getNtypeField($data, 2, 1);
            $trx_id  = $this->getAtypeField($data, 127, 30);
            $mcht_section_code = $this->getAtypeField($data, 157, 1);
            $settle_amount  = $this->getNtypeField($data, 158, 15);

            $supply_amount = round($settle_amount/1.1);
            $vat_amount = $settle_amount - $settle_amount;

            $settle_dt = $this->getNtypeField($data, 175, 6);
            $settle_result_code = $this->getAtypeField($data, 173, 2);
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
                if($settle_result_code === '00')
                {
                    $records[] = [
                        'trans_id'   => $trx_id,
                        'settle_result_code'    => $settle_result_code,
                        'settle_result_msg'     => $this->getSettleMessage($settle_result_code),
                        'card_company_result_code'  => '',
                        'card_company_result_msg'   => '',
                        'mcht_section_code' => $mcht_section_code,
                        'mcht_section_name'  => $this->getMchtSectionName($mcht_section_code),
                        'req_dt'    => null,
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
            '99' => '기타',
        ];
        return isset($card_compnay_codes[$code]) ? $card_compnay_codes[$code] : '알수없는 코드';
    }

    public function registerRequest($brand, $req_date, $mchts, $sub_business_regi_infos)
    {
        return '';
    }

    public function registerResponse($content)
    {
        return '';
    }
}
