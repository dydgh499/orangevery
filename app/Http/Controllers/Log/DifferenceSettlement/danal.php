<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use Carbon\Carbon;

class danal extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
        $this->RQ_PG_NAME = "DANAL";
        $this->RQ_START_FILTER_SIZE  = 470;
        $this->RQ_HEADER_FILTER_SIZE = 488;
        $this->RQ_TOTAL_FILTER_SIZE  = 473;
        $this->RQ_END_FILTER_SIZE    = 491;

        $main_config_name   = 'different_settlement_main_'.$this->service_name;
        $dr_config_name     = 'different_settlement_dr'.$this->service_name;

        config(['filesystems.disks.'.$main_config_name => [
            'driver' => 'sftp',
            'host' => "150.242.135.206",
            'port' => 34567,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "150.242.135.206",
            'port' => 34567,
            'username' => $brand['sftp_id'],
            'password' => $brand['sftp_password'],
            'passive' => false,            
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = $this->connectSFTPServer($dr_config_name, 'dr');
    }

    public function request(Carbon $date, $trans)
    {
        $file_name = $date->copy()->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        // 업체명toDANAL_differ.YYYYMM
        $save_path = "/diff/".$this->brand['rep_mid']."toDANAL_differ.".$file_name;
        return $this->_request($save_path, $req_date, $trans);
    }

    public function response(Carbon $date)
    {
        $file_name = $date->copy()->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        // DANALto업체명_differ.YYYYMM
        $res_path = "/diff/DANALto".$this->brand['rep_mid']."_differ.".$file_name;
        return $this->_response($res_path, $req_date);
    }

    public function registerRequest(Carbon $date, $mchts, $sub_business_regi_infos)
    {
        $file_name = $date->copy()->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        // 업체명toDANAL_differ.YYYYMM
        $save_path = "/Sellerinfo/".$this->brand['rep_mid']."toDANAL.".$file_name;
        return $this->_registerRequest($save_path, $req_date, $mchts, $sub_business_regi_infos);
    }

    public function registerResponse($res_path, $req_date)
    {
        
    }
}
