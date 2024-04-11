<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class nicepay extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = false;
        $this->RQ_PG_NAME = "NICEPAY";
        $this->RQ_START_FILTER_SIZE  = 370;
        $this->RQ_HEADER_FILTER_SIZE = 388;
        $this->RQ_TOTAL_FILTER_SIZE  = 373;
        $this->RQ_END_FILTER_SIZE    = 391;
        
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
        $file_date = $date->format('ymd');
        $req_date = $date->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $file_date."_MD_".$brand_business_num.".00";

        $save_path = "/$file_name";
        return $this->_request($save_path, $req_date, $trans);
    }

    public function response(Carbon $date)
    {
        $file_date = $date->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $file_date."_SD_".$brand_business_num.".00";

        $save_path = "/RECV/$file_name";
        return $this->_response($save_path, $req_date);
    }

    public function registerRequest(Carbon $date, $mchts, $sub_business_regi_infos)
    {
        $file_date = $date->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $file_date."_RS_".$brand_business_num.".00";

        $save_path = "/$file_name";
        return $this->_registerRequest($save_path, $req_date, $mchts, $sub_business_regi_infos);
    }

    public function registerResponse(Carbon $date)
    {
        $file_date = $date->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $file_name = $file_date."_RR_".$brand_business_num.".00";

        $save_path = "/RECV/$file_name";
        return $this->_registerResponse($save_path, $req_date);
    }
}
