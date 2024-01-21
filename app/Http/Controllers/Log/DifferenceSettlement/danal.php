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
        $this->RQ_PG_NAME = "DANAL";
        $this->RQ_START_FILTER_SIZE  = 470;
        $this->RQ_HEADER_FILTER_SIZE = 488;
        $this->RQ_TOTAL_FILTER_SIZE  = 473;
        $this->RQ_END_FILTER_SIZE    = 491;

        $main_config_name   = 'different_settlement_main_'.$this->service_name;
        $dr_config_name     = 'different_settlement_dr'.$this->service_name;
        config(['filesystems.disks.'.$main_config_name => [
            'driver' => 'sftp',
            'host' => "",
            'port' => 5210,
            'username' => $brand['rep_mid'],
            'password' => $brand['rep_mid']."!1234",
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "",
            'port' => 5210,
            'username' => $brand['rep_mid'],
            'password' => $brand['rep_mid']."!1234",
            'passive' => false,            
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = $this->connectSFTPServer($dr_config_name, 'dr');      
    }

    public function request(Carbon $date, $trans)
    {
        $req_date = $date->format('ymd');
        // 업체명toDANAL_differ.YYYYMM
        $save_path = "/업체명toDANAL_differ.".$req_date;
        return parent::request($save_path, $req_date, $trans);
    }

    public function response(Carbon $date)
    {
        $req_date = $date->copy()->format('ymd');
        // DANALto업체명_differ.YYYYMM
        $res_path = "/DANALto업체명_differ.".$req_date;
        return parent::response($res_path, $req_date);
    }
}
