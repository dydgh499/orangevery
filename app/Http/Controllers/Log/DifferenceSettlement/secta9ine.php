<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class secta9ine extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
        $this->RQ_PG_NAME = "X";
        $this->RQ_START_FILTER_SIZE  = 192;
        $this->RQ_HEADER_FILTER_SIZE = 0;
        $this->RQ_TOTAL_FILTER_SIZE  = 0;
        $this->RQ_END_FILTER_SIZE    = 150;
        
        $main_config_name   = 'different_settlement_main_'.$this->service_name;
        $dr_config_name     = 'different_settlement_dr'.$this->service_name;
        config(['filesystems.disks.'.$main_config_name => [
            'driver' => 'sftp',
            'host' => "121.133.126.8",
            'port' => 22,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "121.133.126.12", // 개발서버
            'port' => 22,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = [null, false];
    }

    public function request(Carbon $date, $trans)
    {
        if($this->main_connection_stat)
        {
            $file_date = $date->format('ymd');
            $req_date = $date->format('Ymd');
            $brand_business_num = str_replace('-', '', $this->brand['business_num']);
            $file_name = $brand_business_num."_REQUEST.$file_date";
            $save_path = "/$file_name";

            $total_amount = 0;
            $total_count  = 0;
            $full_record = $this->setStartRecord($req_date);

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
                        $total  = $this->setTotalRecord($count, $amount);
        
                        $full_record .= $data_records.$total;
                        $total_count += ($count + 2);   //header, total records
                        $total_amount += $amount;    
                    }
                }
            }
            $total_count += 2;  // start, end records
            $full_record .= $this->setEndRecord($total_count, $total_amount);
            return $this->upload($save_path, $full_record);
        }
        return false;  
    }

    public function response(Carbon $date)
    {
        $file_date = $date->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $brand_business_num."RECEIVE.$file_date";
        $save_path = "/$file_name";

        return $this->_response($save_path, $req_date);
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
