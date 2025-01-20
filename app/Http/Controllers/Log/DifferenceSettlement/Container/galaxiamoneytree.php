<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Container;

use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerBase;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class galaxiamoneytree extends ContainerBase implements ContainerInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
        $this->RQ_PG_NAME = "X";
        $this->RQ_START_FILTER_SIZE  = 147;
        $this->RQ_HEADER_FILTER_SIZE = 0;
        $this->RQ_TOTAL_FILTER_SIZE  = 0;
        $this->RQ_END_FILTER_SIZE    = 150;

        $main_config_name   = 'different_settlement_main_'.$this->service_name;
        $dr_config_name     = 'different_settlement_dr'.$this->service_name;
        config(['filesystems.disks.'.$main_config_name => [
            'driver' => 'sftp',
            'host' => "119.207.70.214",
            'port' => 22,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "121.156.124.230", 
            'port' => 22,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = $this->connectSFTPServer($dr_config_name, 'dr');      
    }

    public function request(Carbon $date, $trans)
    {
        if($this->main_connection_stat)
        {
            $req_date = $date->format('Ymd');
            $save_path = "/request/".$this->brand['rep_mid']."_REQUEST.".$req_date;

            $full_histories = [];
            $total_amount = 0;
            $total_count  = 0;
            $full_record = $this->setStartRecord($req_date);

            $mids = $trans->pluck($this->PMID_MODE ? 'p_mid' : 'mid')->unique()->all();
            foreach($mids as $mid)
            {
                $mcht_trans = $this->getMidMatchTransctions($trans);
                if(empty($mid) === false)
                {
                    [$data_records, $count, $amount, $temp_histories] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num'], $mid);
                    
                    $full_histories = array_merge($full_histories, $temp_histories);
                    $full_record .= $data_records;
                    $total_count += $count;    
                    $total_amount += $amount;    
                }
                else
                    $full_histories = array_merge($full_histories, $this->service->getMidEmptyHistoryObjects($mcht_trans));
            }
            $full_record .= $this->setEndRecord($total_count, $total_amount);
            if($this->upload($save_path, $full_record))
                return $this->setCreatedAt($full_histories); 
        }
        return [];
    }

    public function response(Carbon $date)
    {
        $download_date = $date->copy()->format('Ymd');
        $download_path = "/receive/".$this->brand['rep_mid']."_RECEIVE.".$download_date;

        $contents = $this->download($download_path);
        if($contents !== "")
        {
            $datas = $this->service->getDataRecord($contents);
            return $this->setGroupbyResultCode($datas, 'settle_result_code');
        }
        else
            return [];
    }

    public function setRegistrationDataRecord(Carbon $date, $sub_business_regi_infos)
    {
        $upload_date = $date->format('Ymd');
        $upload_path = "/request/".$this->brand['rep_mid']."_REQUEST_INFO.".$upload_date;

        [$full_record, $datas] = $this->service->setRegistrationDataRecord($this->brand, $upload_date, $sub_business_regi_infos);
        if($this->upload($upload_path, $full_record, 'merchandise-registration-upload'))
            return $this->setGroupbyResultCode($datas, 'registration_code');
        else
            return [];
    }
    
    public function getRegistrationDataRecord(Carbon $date)
    {
        $download_date = $date->format('Ymd');
        $download_path = "/receive/".$this->brand['rep_mid']."_RECEIVE_INFO.".$download_date;

        $contents = $this->download($download_path, 'merchandise-registration-download');
        if($contents !== "")
        {
            $datas = $this->service->getRegistrationDataRecord($contents);
            return $this->setGroupbyResultCode($datas, 'registration_code');
        }
        else
            return [];
    }
}
