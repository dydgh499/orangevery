<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;

use App\Models\Transaction;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Manager\DifferenceSettlementBase;
use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class welcome1 extends DifferenceSettlementBase implements DifferenceSettlementInterface
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
        $brand_business_num = trim(str_replace('-', '', $brand_business_num));
        $data_histories = [];
        $data_records   = '';
        $total_amount   = 0;
        $total_count    = 0;
        for ($i=0; $i < count($trans); $i++) 
        { 
            $business_num = trim(str_replace('-', '', $trans[$i]->business_num));
            if(strlen($business_num) === 10)
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
                $trx_id         = $this->setAtypeField($trx_id, 15);
                $apr_amount     = $this->setNtypeField($amount, 15);
                $cxl_amount     = $this->setNtypeField($trans[$i]->is_cancel ? $amount : 0, 15);
                $balance        = $this->setNtypeField($trans[$i]->is_cancel ? 0 : $amount, 15);

                $business_num   = $this->setAtypeField($business_num, 10);
                $filter         = $this->setAtypeField('', 312);
    
                $data_record = 
                    $record_type.$appr_type.$trx_id.$apr_amount.$cxl_amount.
                    $balance.$business_num.$filter;

                $data_records .= $data_record."\n";
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
        $records    = [];
        $cur_date   = date('Y-m-d H:i:s');
        $lines = explode("\n", $contents);
        $datas = array_values(array_filter($lines, function($line) {
            return substr($line, 0, 2) === DifferenceSettleHectoRecordType::DATA->value;
        }));
        for ($i=0; $i < count($datas); $i++) 
        {
            $data = $datas[$i];
            $is_cancel  = $this->getNtypeField($data, 2, 1);

            $trx_id  = $this->getAtypeField($data, 3, 15);
            $mcht_section_code = $this->getAtypeField($data, 88, 1);
            $supply_amount  = $this->getNtypeField($data, 89, 15);
            $vat_amount     = $this->getNtypeField($data, 104, 15);
            $settle_amount  = $supply_amount + $vat_amount;
            $settle_dt = $this->getNtypeField($data, 134, 8);
            $settle_result_code = $this->getAtypeField($data, 142, 2);

            $record = $this->getSettlementResponseObejct($trx_id, $settle_result_code, $this->getSettleMessage($settle_result_code), $mcht_section_code, $cur_date);
            if($settle_result_code === '00')
            {
                $record = array_merge(
                    $record,
                    $this->getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
                );
            }
            $records = $this->setSettlementResponseList($records, $record, 'welcome');
        }
        return $this->replaceTransId($records);
    }

    public function getSettleMessage($code)
    {
        $settle_codes = [
            '00' => '정상',
            '01' => '카드사별 구분값 오류', // (미존재 또는 불일치)
            '02' => '매출금액 오류',    // (원매출금액과 하위사업자 매출액 SUM의 불일치)
            '03' => '중복접수',         // (기 처리된 내역을 전송)
            '04' => '원매입 반송',      // (원매출 미존재 또는 매출금액 오류 등)
            '05' => '매입취소구분 오류', // (원매출과 하위매출의 정상/취소 불일치)
            '06' => '매입전송일자 오류',
            '07' => '승인일자 오류',
            '08' => '승인번호 오류',    // (원승인번호에 해당하는 매출 미존재)
            '09' => '가맹점번호 오류1', // (가맹점번호가 SPACE이거나 미등록가맹점)
            '10' => '가맹점번호 오류2',
            '11' => '카드번호 오류',
            '12' => '중간하위사업자 오류',  // (전자금융업자 미해당사업자 등)
            '13' => '차액정산 지연접수',
            '14' => '카드사별 구분값 정상건 반송', // (A,B,C로 구성된 장바구니 거래에서 C의 카드사별 구분값이 오류인 경우 C는 01번 코드로 회신하나, A,B는 카드사별 구분값이 정상임에도 C로 인해 반송되는 것이므로 14번 코드로 구별하여 회신)
            '99' => '기타',
            'A1' => '매입원장에 데이터 없음',   // (acqu_dt, state_cd, tid 확인 필요)
            'A2' => '미매입 거래',
            'A3' => '매입 요청처리중 거래',
            'A4' => '매입 반송 거래',
            'A5' => '매입 보류 거래',
            'A6' => '보류 해제 거래',
            'A9' => '기타오류',
        ];
        return isset($settle_codes[$code]) ? $settle_codes[$code] : '알수없는 코드';
    }

    private function replaceTransId($records)
    {
        if(count($records))
        {
            $trx_ids = array_column($records, 'trans_id');
            $trans = Transaction::whereIn('trx_id', $trx_ids)->get(['id', 'trx_id'])->toArray();
            
            for ($i=0; $i < count($records); $i++) 
            { 
                $idx = array_search($records[$i]['trans_id'], array_column($trans, 'trx_id'));
                if($idx !== false)
                    $records[$i]['trans_id'] = $trans[$idx]['id'];
                else
                    $records[$i]['trans_id'] = null;
            }    
        }
        return $records;
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
