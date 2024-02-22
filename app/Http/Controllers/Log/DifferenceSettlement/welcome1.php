<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class welcome1 extends DifferenceSettlement implements DifferenceSettlementInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
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
            'privateKey' => Storage::disk('local')->get('id_rsa'),
            'passphrase' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = [null, false];
    }

    public function request(Carbon $date, $trans)
    {
        $req_date = $date->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);  // ?
        $save_path = "/upload/dfsttm/send/daff_welcome_".$brand_business_num."_".$req_date."_req";        
        return $this->_request($save_path, $req_date, $trans);
    }

    public function response(Carbon $date)
    {
        $req_date = $date->copy()->format('Ymd');
        // DANALto업체명_differ.YYYYMM
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);  // ?
        $res_path = "/upload/dfsttm/send/daff_welcome_".$brand_business_num."_".$req_date."sply";
        return $this->_response($res_path, $req_date);
    }
}
