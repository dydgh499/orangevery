<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ksnet extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = false;
        $this->RQ_PG_NAME = "X";
        $this->RQ_START_FILTER_SIZE  = 0;
        $this->RQ_HEADER_FILTER_SIZE = 0;
        $this->RQ_TOTAL_FILTER_SIZE  = 0;
        $this->RQ_END_FILTER_SIZE    = 0;
    }

    public function request(Carbon $date, $trans)
    {
        $file_name = $date->format('Ymd');
        $save_path = "/ksnet/REQUEST-$file_name.csv";
        $full_record = "";

        $mids = $trans->pluck($this->PMID_MODE ? 'p_mid' : 'mid')->unique()->all();
        foreach($mids as $mid)
        {
            $mcht_trans = $this->getMidMatchTransctions($trans, $mid);
            if(count($mcht_trans) > 0)
            {
                $_mid = $this->PMID_MODE ? $mcht_trans[0]->p_mid : $mid;
                if(empty($_mid) === false)
                {
                    [$data_records, $count, $amount] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num']);
                    $full_record .= $data_records;
                }
            }
        }
        if(strlen($full_record) > 0)
            return $this->service->moduleUpload($save_path, $this->brand['rep_mid'], $full_record);
        else
            return true;
    }

    public function response(Carbon $date)
    {
        $file_name = $date->format('Ymd');
        $save_path = "/ksnet/RESPONSE-$file_name.csv";
        return $this->service->moduleDownload($save_path, $this->brand['rep_mid']);
    }

    public function registerRequest(Carbon $date, $mchts, $sub_business_regi_infos)
    {
        $file_date = $date->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $file_date."_RS_".$brand_business_num.".00";

        $save_path = "/EDI_MARGIN/$file_name";
        return $this->_registerRequest($save_path, $req_date, $mchts, $sub_business_regi_infos);
    }

    public function registerResponse(Carbon $date)
    {
        $file_date = $date->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $file_date."_RR_".$brand_business_num.".00";

        $save_path = "/EDI_MARGIN/RECV/$file_name";
        return $this->_registerResponse($save_path, $req_date);
    }
}
