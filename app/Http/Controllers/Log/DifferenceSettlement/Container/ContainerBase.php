<?php

namespace App\Http\Controllers\Log\DifferenceSettlement\Container;

use App\Http\Traits\Log\DifferenceSettlement\FileRWTrait;
use App\Enums\DifferenceSettleHectoRecordType;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ContainerBase
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

        $path = "App\Http\Controllers\Log\DifferenceSettlement\Component\\".$this->service_name;
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
        if(env('APP_ENV') === 'local')
        {
            $connection = Storage::disk('public');
            $connection_stat = true;
        }
        else
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
                logging(['type'=>$type], $this->service_name."\t $type \t"."difference-settlement-connection (X)");
            }    
        }
        return [$connection, $connection_stat];
    }

    protected function getMidMatchTransctions($trans, $mid)
    {
        return $trans->filter(function ($tran)use ($mid) {
            if($this->PMID_MODE)
                return $tran->p_mid === $mid;
            else
                return $tran->mid === $mid;
        })->values();
    }

    public function setCreatedAt($data_histories)
    {
        $cur_at = date("Y-m-d H:i:s");
        for ($i=0; $i < count($data_histories) ; $i++) 
        { 
            $data_histories[$i]['created_at'] = $cur_at;
            $data_histories[$i]['updated_at'] = $cur_at;

        }
        return $data_histories;
    }
    
    public function setUpdatedAt($data_histories)
    {
        $cur_at = date("Y-m-d H:i:s");
        for ($i=0; $i < count($data_histories) ; $i++) 
        { 
            $data_histories[$i]['updated_at'] = $cur_at;

        }
        return $data_histories;
    }

    protected function upload($save_path, $full_record, $message = 'difference-settlement-upload')
    {
        if($this->main_connection_stat)
        {
            if($this->main_sftp_connection->put($save_path, $full_record))
            {
                logging(['save_path'=>$save_path, 'line' => count(explode("\n", $full_record))], $this->service_name."\t main \t"."$message (O)");
                return true;
            }
            else
            {
                error(['save_path'=>$save_path, 'line' => count(explode("\n", $full_record))], $this->service_name."\t main \t"."$message (X)");
                return false;
            }    
        }
        else
            return false;
    }

    protected function download($download_path, $message = 'difference-settlement-download')
    {
        try
        {
            $connection_type = "";
            if($this->main_connection_stat && $this->main_sftp_connection->exists($download_path))
            {
                $connection_type = 'MAIN';
                $contents  = $this->main_sftp_connection->get($download_path);
            }
            else if($this->dr_connection_stat && $this->dr_sftp_connection->exists($download_path))
            {
                $connection_type = 'DR';
                $contents = $this->dr_sftp_connection->get($download_path);
            }
            else
                $contents = "";
            if($contents !== "")
            {
                logging(['download_path'=>$download_path, 'line' => count(explode("\n", $contents))], $this->service_name."\t $connection_type \t"."$message (O)");
                return $contents;   
            }
            else
            {
                error(['download_path'=>$download_path, 'line' => count(explode("\n", $contents))], $this->service_name."\t $connection_type \t"."$message (X)");
                return "";
            }
        }
        catch(\Throwable $e)
        {
            error(['download_path'=>$download_path, 'error' => $e->getMessage()], $this->service_name."\t $connection_type \t"."$message (X)");
            return [];
        }
    }

    public function setGroupbyResultCode($records, $key)
    {
        $grouped_records = [];
        foreach ($records as $record) 
        {
            $settle_result_code = $record[$key];
            if (!isset($grouped_records[$settle_result_code]))
                $grouped_records[$settle_result_code] = [];

            $grouped_records[$settle_result_code][] = $record;
        }
        return $grouped_records;
    }

    public function setStartRecord($req_date)
    {
        if($this->service_name === 'secta9ine')
        {
            $record_type    = $this->setAtypeField('HD', 2);
            $create_dt      = $this->setAtypeField(date('ymd'), 6);
            $filter         = $this->setAtypeField('', $this->RQ_START_FILTER_SIZE);
            return $record_type.$create_dt.$filter."\r\n";  
        }
        else if($this->service_name == 'galaxiamoneytree')
        {
            $record_type    = $this->setAtypeField('HD', 2);
            $create_dt      = $this->setAtypeField(date('Ymd'), 8);
            $rep_mid        = $this->setAtypeField($this->brand['rep_mid'], 20);
            $filter         = $this->setAtypeField('', $this->RQ_START_FILTER_SIZE);
            return $record_type.$create_dt.$rep_mid.$filter."\r\n";  
        }
        else
        {
            $brand_business_num = str_replace('-', '', $this->brand['business_num']);
            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::START->value, 2);
            $req_date       = $this->setNtypeField($req_date, 8);
            $brand_business_num = $this->setAtypeField($brand_business_num, 10);

            $pg_type = $this->service_name === 'welcome' ? '' : $this->setAtypeField($this->RQ_PG_NAME, 10);
            $filter  = $this->setAtypeField('', $this->RQ_START_FILTER_SIZE);
            return $record_type.$req_date.$brand_business_num.$pg_type.$filter."\r\n";         
        }
    }

    public function setHeaderRecord($rep_mid)
    {
        $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::HEADER->value, 2);
        $rep_mid        = $this->setAtypeField($rep_mid, 10);
        $filter         = $this->setAtypeField('', $this->RQ_HEADER_FILTER_SIZE);
        return $record_type.$rep_mid.$filter."\r\n";    
    }

    public function setEndRecord($total_count, $total_amount)
    {
        if($this->service_name == 'galaxiamoneytree' || $this->service_name === 'secta9ine')
        {
            $record_type    = $this->setAtypeField('TR', 2);
            $total_count    = $this->setNtypeField($total_count, 7);
            $filter         = $this->setAtypeField('', $this->RQ_END_FILTER_SIZE);
            if($total_amount < 0)
                $total_amount = "-".$this->setNtypeField(abs($total_amount), 17);
            else
                $total_amount = $this->setNtypeField($total_amount, 18);
            return $record_type.$total_count.$total_amount.$filter."\r\n";
        }
        else
        {
            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::END->value, 2);
            $total_count    = $this->setNtypeField($total_count, 7);
            $filter         = $this->setAtypeField('', $this->RQ_END_FILTER_SIZE);
            return $record_type.$total_count.$filter."\r\n";
        }
    }
}
