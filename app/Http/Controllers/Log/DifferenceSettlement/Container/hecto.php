<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Container;

use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerBase;
use App\Enums\DifferenceSettleHectoRecordType;
use Carbon\Carbon;

class hecto extends ContainerBase implements ContainerInterface
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
        $dr_config_name     = 'different_settlement_dr_'.$this->service_name;
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
            'host' => "14.34.14.26",    // DR
            'port' => 5210,
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
        $total_records .= $this->setAtypeField($total_amount, 18);
        $total_records .= $this->setAtypeField('', $this->RQ_TOTAL_FILTER_SIZE)."\r\n";
        return $total_records;
    }

    public function request(Carbon $date, $trans)
    {
        if($this->main_connection_stat)
        {
            $req_date = $date->format('Ymd');
            $save_path = "/edi_req/ST_PRFT_REQ_".$req_date;

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
                    $total_count += $count;    
                    $total_amount += $amount;
                }
                else
                    $full_histories = array_merge($full_histories, $this->service->getMidEmptyHistoryObjects($mcht_trans));
            }
            $full_record .= $this->setEndRecord($total_count, $total_amount);
            if($this->dr_connection_stat)
            {
                if($this->dr_sftp_connection->put($save_path, $full_record))
                    logging(['save_path'=>$save_path], $this->service_name."\t DR \t"."difference-settlement-upload (O)");
                else
                    logging(['save_path'=>$save_path], $this->service_name."\t DR \t"."difference-settlement-upload (X)");
            }
            if($this->upload($save_path, $full_record))
                return $this->setCreatedAt(array_values($full_histories));
        }
        return [];
    }

    public function response(Carbon $date)
    {
        $download_date = $date->copy()->format('Ymd');
        $download_path = "/edi_rsp/ST_PRFT_RSP_".$download_date;
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
        $upload_path = "/edi_req/ST_BIZREG_REQ_".$upload_date;

        [$full_record, $datas] = $this->service->setRegistrationDataRecord($this->brand, $upload_date, $sub_business_regi_infos);
        if($this->upload($upload_path, $full_record, 'merchandise-registration-upload'))
            return $this->setGroupbyResultCode($datas, 'registration_code');
        else
            return [];
    }

    public function getRegistrationDataRecord(Carbon $date)
    {
        $download_date = $date->format('Ymd');
        $download_path = "/edi_req/ST_BIZREG_RSP_".$download_date;

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
