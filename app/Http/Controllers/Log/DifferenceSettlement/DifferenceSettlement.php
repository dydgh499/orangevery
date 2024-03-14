<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DifferenceSettlement
{
    use FileRWTrait;
    protected $brand   = null;
    protected $service = null;
    protected $service_name = null;

    protected $main_sftp_connection, $dr_sftp_connection;
    protected $main_connection_stat, $dr_connection_stat;

    protected $PMID_MODE;
    protected $RQ_START_FILTER_SIZE, $RQ_HEADER_FILTER_SIZE, $RQ_TOTAL_FILTER_SIZE, $RQ_END_FILTER_SIZE;
    protected $RQ_PG_NAME;

    public function __construct($brand)
    {
        $this->brand = $brand;
        $this->service_name = basename(str_replace('\\', '/', get_class($this)));

        $path = "App\Http\Controllers\Log\DifferenceSettlement\Manager\\".$this->service_name;
        try
        {
            $this->service = new $path();
        }
        catch(\Throwable $e)
        {   // pg사 발견못함
            logging(['message' => $e->getMessage()], 'DifferenceSettlement - PG사가 없습니다.('.$this->service_name.")");
        }
    }
    
    protected function connectSFTPServer($service_name, $type)
    {
        try
        {
            $connection = Storage::disk($service_name);
            $connection->directories(); //connection check
            $connection_stat = true;
        }
        catch(\Throwable $e)
        {
            $connection = null;
            $connection_stat = false;
            logging(['type'=>$type], 'connection fail');
        }
        return [$connection, $connection_stat];
    }

    protected function _request($save_path, $req_date, $trans)
    {
        $result     = false;
        $pmid_mode  = $this->service_name === 'danal' ? 'p_mid' : 'mid';

        $mids = $trans->pluck($pmid_mode ? 'p_mid' : 'mid')->unique()->all();
        $sub_count = 0;
        $total_count = 0;
        $full_record = $this->setStartRecord($req_date);

        foreach($mids as $mid)
        {
            $mcht_trans = $trans->filter(function ($tran) use ($mid, $pmid_mode) {
                if($pmid_mode === 'p_mid')
                    return $tran->p_mid === $mid;
                else
                    return $tran->mid === $mid;
            })->values();

            if(count($mcht_trans) > 0)
            {
                $_mid = $pmid_mode === 'p_mid' ? $mcht_trans[0]->p_mid : $mid;
                if($_mid === "")
                    continue;

                $header = $this->setHeaderRecord($_mid);
                [$data_records, $count, $amount] = $this->service->setDataRecord($mcht_trans, $this->brand['business_num']);
                $total  = $this->setTotalRecord($count, $amount);

                $full_record .= $header.$data_records.$total;
                $total_count += $count;                    

                if($this->service_name === 'danal')
                    $sub_count += 2;  //header, total records
            }
        }
        if($this->service_name === 'danal')
            $sub_count += 2;  // start, end records
        $full_record .= $this->setEndRecord($total_count + $sub_count);

        if($this->main_connection_stat)
            $result = $this->main_sftp_connection->put($save_path, $full_record);
        if($this->dr_connection_stat)
            $result = $this->dr_sftp_connection->put($save_path, $full_record);
        logging(['result'=>$result, 'save_path'=>$save_path], $this->service_name.'-difference-settlement-request');
        return $result;
    }

    protected function _response($res_path, $req_date)
    {
        try
        {
            if($this->main_connection_stat && $this->main_sftp_connection->exists($res_path))
                $contents = $this->main_sftp_connection->get($res_path);
            else if($this->dr_connection_stat && $this->dr_sftp_connection->exists($res_path))
                $contents = $this->dr_sftp_connection->get($res_path);
            else
                $contents = null;

            $datas = $contents ? $this->service->getDataRecord($contents) : [];
            logging(['date'=>$req_date, 'datas'=>$datas], $this->service_name.'-difference-settlement-response');
            return $datas;
        }
        catch(\Throwable $e)
        {
            logging(['date'=>$req_date, 'datas'=>[]], $this->service_name.'-difference-settlement-response('. $e->getMessage().")");
            return null;
        }
    }

    private function setStartRecord($req_date)
    {
        if($this->service_name == 'galaxiamoneytree')
            return '';
        else
        {
            $brand_business_num = str_replace('-', '', $this->brand['business_num']);
            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::START->value, 2);
            $req_date       = $this->setNtypeField($req_date, 8);
            $brand_business_num = $this->setAtypeField($brand_business_num, 10);
            $pg_type        = $this->setAtypeField($this->RQ_PG_NAME, 10);
            $filter         = $this->setAtypeField('', $this->RQ_START_FILTER_SIZE);
            return $record_type.$req_date.$brand_business_num.$pg_type.$filter."\r\n";                
        }
    }

    private function setHeaderRecord($rep_mid)
    {
        if($this->service_name == 'galaxiamoneytree')
            return $this->service->setHeaderRecord($rep_mid, $this->RQ_HEADER_FILTER_SIZE);
        else
        {
            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::HEADER->value, 2);
            $rep_mid        = $this->setAtypeField($rep_mid, 10);
            $filter         = $this->setAtypeField('', $this->RQ_HEADER_FILTER_SIZE);
            return $record_type.$rep_mid.$filter."\r\n";    
        }
    }

    private function setTotalRecord($total_count, $total_amount)
    {
        if($this->service_name == 'galaxiamoneytree')
            return $this->service->setTotalRecord($this->RQ_TOTAL_FILTER_SIZE, $total_count, $total_amount);
        else
        {
            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::TOTAL->value, 2);
            if($this->service_name == 'welcome1')
                $total_count    = $this->setAtypeField($total_count, 7);
            else
                $total_count    = $this->setNtypeField($total_count, 7);

            if($this->service_name == 'hecto' || $this->service_name == 'welcome1')
                $total_amount   = $this->setAtypeField($total_amount, 18);
            else // danal
                $total_amount   = $this->setNtypeField($total_amount, 18);

            $filter         = $this->setAtypeField('', $this->RQ_TOTAL_FILTER_SIZE);
            return $record_type.$total_count.$total_amount.$filter."\r\n";    
        }
    }

    private function setEndRecord($total_count)
    {
        if($this->service_name == 'galaxiamoneytree')
            return '';
        else
        {
            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::END->value, 2);
            $total_count    = $this->setNtypeField($total_count, 7);
            $filter         = $this->setAtypeField('', $this->RQ_END_FILTER_SIZE);
            return $record_type.$total_count.$filter."\r\n";    
        }
    }

    public function _registerRequest($save_path, $req_date, $mchts, $sub_business_regi_infos)
    {
        $full_record = $this->service->registerRequest($this->brand, $req_date, $mchts, $sub_business_regi_infos);
        if($this->main_connection_stat)
            $result = $this->main_sftp_connection->put($save_path, $full_record);
        if($this->dr_connection_stat)
            $result = $this->dr_sftp_connection->put($save_path, $full_record);
        logging(['result'=>$result, 'save_path'=>$save_path], $this->service_name.'-sub-business-registration-request');
        return $result;
    }

    public function _registerResponse($res_path, $req_date)
    {
        
    }
}
