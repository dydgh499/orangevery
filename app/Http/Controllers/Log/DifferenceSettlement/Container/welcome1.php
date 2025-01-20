<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Container;

use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerInterface;
use App\Http\Controllers\Log\DifferenceSettlement\Container\ContainerBase;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class welcome1 extends ContainerBase implements ContainerInterface
{
    public function __construct($brand)
    {
        parent::__construct($brand);
        $this->PMID_MODE  = true;
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
            'protectedKey' => Storage::disk('local')->get('id_rsa'),
            'passphrase' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        config(['filesystems.disks.'.$dr_config_name => [
            'driver' => 'sftp',
            'host' => "118.130.130.27", // 개발서버
            'port' => 5555,
            'username' => $brand['sftp_id'],
            'protectedKey' => Storage::disk('local')->get('id_rsa'),
            'passphrase' => $brand['sftp_password'],
            'passive' => false,
        ]]);
        [$this->main_sftp_connection, $this->main_connection_stat] = $this->connectSFTPServer($main_config_name, 'main');
        [$this->dr_sftp_connection, $this->dr_connection_stat] = [null, false];
    }

    protected function setTotalRecord($total_count, $total_amount)
    {
        $total_records  = $this->setAtypeField(DifferenceSettleHectoRecordType::TOTAL->value, 2);
        $total_records .= $this->setAtypeField($total_count, 7);
        $total_records .= $this->setAtypeField('', $this->RQ_TOTAL_FILTER_SIZE)."\r\n";
        return $total_records;
    }

    public function request(Carbon $date, $trans)
    {
        if($this->main_connection_stat)
        {
            $req_date = $date->format('Ymd');
            $brand_business_num = str_replace('-', '', $this->brand['business_num']);  // ?
            $save_path = "/upload/dfsttm/send/daff_welcome_".$brand_business_num."_".$req_date."_req";        

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
            if($this->upload($save_path, $full_record))
                return $this->setCreatedAt($full_histories);
        }
        return [];
    }

    public function response(Carbon $date)
    {
        $download_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $download_path = "/upload/dfsttm/send/daff_welcome_".$brand_business_num."_".$download_date."sply";

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
        $upload_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $upload_path = "/upload/dfsttm/send/merc_welcome_".$brand_business_num."_".$upload_date."req";

        [$full_record, $datas] = $this->service->setRegistrationDataRecord($this->brand, $upload_date, $sub_business_regi_infos);
        if($this->upload($upload_path, $full_record, 'merchandise-registration-upload'))
            return $this->setGroupbyResultCode($datas, 'registration_code');
        else
            return [];
    }

    public function getRegistrationDataRecord(Carbon $date)
    {
        $download_date = $date->copy()->format('Ymd');
        $brand_business_num = str_replace('-', '', $this->brand['business_num']);
        $download_path = "/upload/dfsttm/recv/merc_welcome_".$brand_business_num."_".$download_date."_rslt";

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
