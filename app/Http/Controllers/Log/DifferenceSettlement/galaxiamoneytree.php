<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class galaxiamoneytree extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
        $this->RQ_PG_NAME = "X";
        $this->RQ_START_FILTER_SIZE  = 0;
        $this->RQ_HEADER_FILTER_SIZE = 147;
        $this->RQ_TOTAL_FILTER_SIZE  = 150;
        $this->RQ_END_FILTER_SIZE    = 0;

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

            $total_amount = 0;
            $total_count  = 0;
            $full_record = $this->setStartRecord($req_date);

            $mids = $trans->pluck($this->PMID_MODE ? 'p_mid' : 'mid')->unique()->all();
            foreach($mids as $mid)
            {
                $mcht_trans = $this->getMidMatchTransctions($trans);
                if(count($mcht_trans) > 0)
                {
                    $_mid = $this->PMID_MODE ? $mcht_trans[0]->p_mid : $mid;
                    if(empty($_mid) === false)
                    {
                        [$data_records, $count, $amount] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num']);
                        $full_record .= $data_records;
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
        $res_path = "/receive/".$this->brand['rep_mid']."_RECEIVE.".$req_date;
        return $this->_response($res_path, $req_date);
    }

    public function registerRequest(Carbon $date, $mchts, $sub_business_regi_infos)
    {
        $req_date = $date->format('Ymd');
        $save_path = "/request/".$this->brand['rep_mid']."_REQUEST_INFO.".$req_date;
        return $this->_registerRequest($save_path, $req_date, $mchts, $sub_business_regi_infos);
    }
    
    public function registerResponse($res_path, $req_date)
    {
        
    }
}
