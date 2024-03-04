<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use Carbon\Carbon;

class hecto extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = false;
        $this->RQ_PG_NAME = "SETTLEBANK";
        $this->RQ_START_FILTER_SIZE  = 370;
        $this->RQ_HEADER_FILTER_SIZE = 388;
        $this->RQ_TOTAL_FILTER_SIZE  = 373;
        $this->RQ_END_FILTER_SIZE    = 391;

        $main_config_name   = 'different_settlement_main_'.$this->service_name;
        $dr_config_name     = 'different_settlement_dr'.$this->service_name;
        config(['filesystems.disks.'.$main_config_name => [
            'driver' => 'sftp',
            'host' => "61.252.169.33",
            'port' => 5210,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "14.34.14.26",
            'port' => 5210,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,            
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = $this->connectSFTPServer($dr_config_name, 'dr');        
    }
    
    public function request(Carbon $date, $trans)
    {
        $req_date = $date->format('Ymd');
        $save_path = "/edi_req/ST_PRFT_REQ_".$req_date;
        return $this->_request($save_path, $req_date, $trans);
    }

    public function response(Carbon $date)
    {
        $req_date = $date->copy()->format('Ymd');
        $res_path = "/edi_rsp/ST_PRFT_RSP_".$req_date;
        return $this->_response($res_path, $req_date);
    }

    public function registerRequest(Carbon $date, $trans)
    {
        $req_date = $date->format('Ymd');
        $save_path = "/edi_req/ST_PRFT_REQ_".$req_date;
        //return $this->_registerRequest($save_path, $req_date, $mchts);
        return true;
    }
}
