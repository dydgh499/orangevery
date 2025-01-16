<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Manager;
use Carbon\Carbon;

class DifferenceSettlementBase
{
    public function getBaseSettleCodes()
    {
        return [
            '-101'  => 'MID 누락',
            '-100'  => '가맹점 사업자번호 오기입',
            '50'    => '업로드 완료',
            '51'    => '재업로드',
        ];
    }

    public function getMchtSectionName($code)
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

    public function getSettlementResponseObejct($trans_id, $settle_result_code, $settle_result_msg, $mcht_section_code, $cur_date)
    {
        return [
            'trans_id'              => $trans_id,
            'settle_result_code'    => $settle_result_code,
            'settle_result_msg'     => $settle_result_msg,
            'mcht_section_code'     => $mcht_section_code,
            'updated_at'            => $cur_date,
        ];
    }

    public function getSettlementResponseSuccess($settle_dt, $supply_amount, $vat_amount, $settle_amount)
    {
        $record = [
            'settle_result_code' => '00',
            'settle_dt'     => $settle_dt,
            'supply_amount' => $supply_amount,
            'vat_amount'    => $vat_amount,
            'settle_amount' => $settle_amount,
        ];
        try
        {
            $record['settle_dt']  = Carbon::createFromFormat('Ymd', (string)$settle_dt)->format('Y-m-d');
        }
        catch(\Throwable $e)
        {
            $record['settle_dt'] = null;
        }
        return $record;
    }
    
    public function setSettlementResponseList($records, $record, $service_name)
    {
        if($record['trans_id'] !== 0)
            $records[] = $record;
        else
            error(['message' => 'trans_id is empty'], $service_name."\t main \t"."difference-settlement-response");
        return $records;
    }

    public function getSettlementHistoryObejct($trx_id, $settle_result_code = '50')
    {
        $settle_result_codes = $this->getBaseSettleCodes();
        return [
            'trans_id'   => $trx_id,
            'settle_result_code'    => $settle_result_code,
            'settle_result_msg'     => $settle_result_codes[$settle_result_code],
            'mcht_section_code'     => null,
            'req_dt'    => date('Y-m-d'),
            'settle_dt' => null,
            'supply_amount' => 0,
            'vat_amount'    => 0,
            'settle_amount' => 0,
        ];
    }

    public function getMidEmptyHistoryObjects($trans)
    {
        $datas = [];
        foreach($mcht_trans as $tran)
        {
            $datas[] = $this->getSettlementHistoryObejct($tran->id, '-101');
        }
    }
}
