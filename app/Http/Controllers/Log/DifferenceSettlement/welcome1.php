<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class welcome1 extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
        $this->RQ_PG_NAME = "WELCOMEPAY";
        $this->RQ_START_FILTER_SIZE  = 320;
        $this->RQ_HEADER_FILTER_SIZE = 338;
        $this->RQ_TOTAL_FILTER_SIZE  = 323;
        $this->RQ_END_FILTER_SIZE    = 341;
        
        $main_config_name   = 'different_settlement_main_'.$this->service_name;
        $dr_config_name     = 'different_settlement_dr'.$this->service_name;
        config(['filesystems.disks.'.$main_config_name => [
            'driver' => 'sftp',
            'host' => "118.129.171.153",
            'port' => 5555,
            'username' => $brand['sftp_id'],
            'protectedKey' => Storage::disk('local')->get('id_rsa'),
            'passphrase' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "118.130.130.27", // 개발서버
            'port' => 5555,
            'username' => $brand['sftp_id'],
            'protectedKey' => Storage::disk('local')->get('id_rsa'),
            'passphrase' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = [null, false];
    }

    protected function setTotalRecord($total_count, $total_amount)
    {
        $total_records  = $this->setAtypeField(DifferenceSettleHectoRecordType::TOTAL->value, 2);
        $total_records .= $this->setAtypeField($total_count, 7);
        $total_records .= $this->setAtypeField('', $this->RQ_TOTAL_FILTER_SIZE)."\r\n";
        return $total_records;
    }

    public function request(Carbon $date, $trans)
    {
        if($this->main_connection_stat)
        {
            $req_date = $date->format('Ymd');
            $brand_business_num = str_replace('-', '', $this->brand['business_num']);  // ?
            $save_path = "/upload/dfsttm/send/daff_welcome_".$brand_business_num."_".$req_date."_req";        

            $total_amount = 0;
            $total_count  = 0;
            $full_record = $this->setStartRecord($req_date);

            $mids = $trans->pluck($this->PMID_MODE ? 'p_mid' : 'mid')->unique()->all();
            foreach($mids as $mid)
            {
                $mcht_trans = $this->getMidMatchTransctions($trans, $mid);
                if(count($mcht_trans) > 0)
                {
                    if(empty($mid) === false)
                    {
                        $header = $this->setHeaderRecord($mid);
                        [$data_records, $count, $amount] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num'], $mid);
                        $total  = $this->setTotalRecord($count, $amount);
    
                        $full_record .= $header.$data_records.$total;
                        $total_count += $count;    
                        $total_amount += $amount;
                    }
                }
            }
            $full_record .= $this->setEndRecord($total_count, $total_amount);
            return $this->upload($save_path, $full_record);
        }
        return false;
    }

    public function response(Carbon $date)
    {
        $req_date = $date->copy()->format('Ymd');
        // DANALto업체명_differ.YYYYMM
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);  // ?
        $res_path = "/upload/dfsttm/send/daff_welcome_".$brand_business_num."_".$req_date."sply";
        return $this->_response($res_path, $req_date);
    }

    public function registerRequest(Carbon $date, $mchts, $sub_business_regi_infos)
    {
        $req_date = $date->copy()->format('Ymd');
        // DANALto업체명_differ.YYYYMM
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);  // ?
        $save_path = "/upload/dfsttm/send/merc_welcome_".$brand_business_num."_".$req_date."req";
        return $this->_registerRequest($save_path, $req_date, $mchts, $sub_business_regi_infos);
    }

    public function registerResponse(Carbon $date)
    {
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $res_path = "/upload/dfsttm/recv/merc_welcome_".$brand_business_num."_".$req_date."_rslt";
        return $this->_registerResponse($res_path, $req_date);
    }
}
