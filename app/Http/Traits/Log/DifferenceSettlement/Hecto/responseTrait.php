<?php

namespace App\Http\Traits\Log\DifferenceSettlement\Hecto;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

trait responseTrait
{    
    private function getNtypeField($data, $s_idx, $length)
    {
        return (int)substr($data, $s_idx, $length);
    }

    private function getAtypeField($data, $s_idx, $length)
    {
        return ltrim(substr($data, $s_idx, $length));
    }

    private function getSettleMessage($code)
    {
        $settle_codes = [
            '0000' => '정상',
            '1101' => '상점ID 미 존재',
            '1102' => '차액 정산 비대상 가맹점',
            '1103' => '영중소 사업자번호 아님', 
            '1201' => '해당거래 미 존재', 
            '1202' => '원거래 매입금액 불일치', 
            '1203' => '원거래 매입금액과 하위사업자 매출액 불일치', 
            '1301' => '매입전 취소 거래', 
            '1302' => '당일 취소 거래', 
            '1401' => '차액 정산 매입결과 미수신', 
            '9999' => '기타오류 (PG사 문의)(ex. 중복 전송, 최종하위사업자번호 오류 등)',
        ];
        return isset($settle_codes[$code]) ? $settle_codes[$code] : '알수없는 코드';
    }

    private function getCardCompanyMessage($code)
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

    private function getDataRecord($contents)
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
            $add_field  = $this->getAtypeField($data, 219, 40);
            $mcht_section_code = $this->getAtypeField($data, 259, 1);
            $supply_amount  = $this->getNtypeField($data, 260, 15);
            $vat_amount     = $this->getNtypeField($data, 275, 15);
            $settle_amount  = $this->getNtypeField($data, 290, 15);
            $settle_dt = $this->getNtypeField($data, 305, 8);
            $settle_result_code = $this->getAtypeField($data, 313, 4);
            $card_company_result_code = $this->getAtypeField($data, 317, 2);
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
    
                $records[] = [
                    'trans_id'   => $add_field,
                    'settle_result_code'    => $settle_result_code,
                    'settle_result_msg'     => $this->getSettleMessage($settle_result_code),
                    'card_company_result_code'  => $card_company_result_code,
                    'card_company_result_msg'   => $this->getCardCompanyMessage($card_company_result_code),
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
        return $records;
    }
}
