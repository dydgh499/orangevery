<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementInterface;
use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlement;
use App\Enums\DifferenceSettleHectoRecordType;
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

        $main_config_name   = 'main_'.$this->service_name;
        $dr_config_name     = 'dr_'.$this->service_name;

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

    private function setTotalRecord($total_count, $total_amount)
    {
        $total_records  = $this->setAtypeField(DifferenceSettleHectoRecordType::TOTAL->value, 2);
        $total_records .= $this->setNtypeField($total_count, 7);
        if($total_amount < 0)
            $total_records .= "-".$this->setNtypeField(abs($total_amount), 17);
        else
            $total_records .= $this->setNtypeField($total_amount, 18);;
        $total_records .= $this->setAtypeField('', $this->RQ_TOTAL_FILTER_SIZE)."\r\n";
        return $total_records;
    }

    public function request(Carbon $date, $trans)
    {
        if($this->main_connection_stat)
        {
            $file_name = $date->copy()->format('ymd');
            $req_date = $date->copy()->format('Ymd');
            // 업체명toDANAL_differ.YYYYMM
            $save_path = "/diff/".$this->brand['rep_mid']."toDANAL_differ.".$file_name;
            
            $full_histories = [];
            $total_amount = 0;
            $total_count  = 0;
            $full_record = $this->setStartRecord($req_date);

            $mids = $trans->pluck($this->PMID_MODE ? 'p_mid' : 'mid')->unique()->all();
            foreach($mids as $mid)
            {
                $mcht_trans = $this->getMidMatchTransctions($trans, $mid);
                if(empty($mid) === false)
                {
                    $header = $this->setHeaderRecord($mid);
                    [$data_records, $count, $amount, $temp_histories] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num'], $mid);
                    $total  = $this->setTotalRecord($count, $amount);

                    $full_histories = array_merge($full_histories, $temp_histories);
                    $full_record .= $header.$data_records.$total;
                    $total_count += ($count + 2);   //header, total records
                    $total_amount += $amount;    
                }
                else
                    $full_histories = array_merge($full_histories, $this->service->getMidEmptyHistoryObjects($mcht_trans));
            }

            $total_count += 2;  // start, end records
            $full_record .= $this->setEndRecord($total_count, $total_amount);
            if($this->upload($save_path, $full_record))
                return $this->setCreatedAt($full_histories);
        }
        return [];
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

    public function registerResponse(Carbon $date)
    {
        $file_name = $date->copy()->format('ymd');
        $req_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $res_path = "/Sellerinfo/DANALto".$this->brand['rep_mid'].".".$file_name;
        return $this->_registerResponse($res_path, $req_date);
    }
}
