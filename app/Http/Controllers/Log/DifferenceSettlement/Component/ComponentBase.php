<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Component;

use App\Http\Controllers\Log\DifferenceSettlement\Component\MerchandiseRegistrationBase;
use Carbon\Carbon;

class ComponentBase extends MerchandiseRegistrationBase
{
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
