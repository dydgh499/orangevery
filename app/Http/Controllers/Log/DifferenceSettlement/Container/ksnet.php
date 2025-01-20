<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Container;

use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerBase;
use App\Enums\DifferenceSettleHectoRecordType;
use App\Models\Merchandise\PaymentModule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ksnet extends ContainerBase implements ContainerInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
        $this->RQ_PG_NAME = "X";
        $this->RQ_START_FILTER_SIZE  = 0;
        $this->RQ_HEADER_FILTER_SIZE = 0;
        $this->RQ_TOTAL_FILTER_SIZE  = 0;
        $this->RQ_END_FILTER_SIZE    = 0;
    }

    public function request(Carbon $date, $trans)
    {
        $file_name = $date->format('Ymd');
        $mids = $trans->pluck($this->PMID_MODE ? 'p_mid' : 'mid')->unique()->all();
        $full_histories = [];
        foreach($mids as $mid)
        {
            $full_record = "";
            $save_path = "/ksnet/REQUEST-$file_name-$mid.csv";
            $mcht_trans = $this->getMidMatchTransctions($trans, $mid);
            if(empty($mid) === false)
            {
                [$data_records, $count, $amount, $temp_histories] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num'], $mid);
                $full_record .= $data_records;

                $full_histories = array_merge($full_histories, $temp_histories);
                if(strlen($full_record) > 0)
                    $this->service->moduleUpload($save_path, $mid, $full_record);
            }
            else
                $full_histories = array_merge($full_histories, $this->service->getMidEmptyHistoryObjects($mcht_trans));
        }

        return $this->setCreatedAt($full_histories);  
    }

    public function response(Carbon $date)
    {
        $file_name = $date->format('Ymd');

        $datas  = [];
        $col    = $this->PMID_MODE ? 'p_mid' : 'mid';

        $payment_modules = PaymentModule::join('payment_gateways', 'payment_modules.pg_id', '=', 'payment_gateways.id')
            ->where('payment_gateways.pg_type', 37)
            ->groupBy("payment_modules.$col")
            ->get(["payment_modules.$col"])
            ->toArray();

        foreach($payment_modules as $payment_module)
        {
            $mid = $payment_module[$col];
            $save_path = "/ksnet/RESPONSE-$file_name-$mid.csv";
            if (preg_match('/^[a-zA-Z0-9_]+$/', $mid) && strlen($mid) === 10)
                $datas = array_merge($datas, $this->service->moduleDownload($save_path, $mid));
        }
        return $datas;
    }

    public function setRegistrationDataRecord(Carbon $date, $sub_business_regi_infos)
    {
        return [];
    }

    public function getRegistrationDataRecord(Carbon $date)
    {
        return [];
    }
}
