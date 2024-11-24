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
        if(env('APP_ENV') === 'local')
            return [null, true];
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

    protected function upload($save_path, $full_record)
    {
        if($this->main_connection_stat)
        {
            if($this->main_sftp_connection->put($save_path, $full_record))
            {
                logging(['save_path'=>$save_path], $this->service_name."\t main \t"."difference-settlement-request (O)");
                return true;
            }
            else
            {
                error(['save_path'=>$save_path], $this->service_name."\t main \t"."difference-settlement-request (X)");
                return false;
            }    
        }
        else
            return false;
    }

    protected function _response($res_path, $req_date)
    {
        try
        {
            $connection_type = "";
            if($this->main_connection_stat && $this->main_sftp_connection->exists($res_path))
            {
                $connection_type = 'MAIN';
                $contents = $this->main_sftp_connection->get($res_path);
            }
            else if($this->dr_connection_stat && $this->dr_sftp_connection->exists($res_path))
            {
                $connection_type = 'DR';
                $contents = $this->dr_sftp_connection->get($res_path);
            }
            else
                return [];

            $datas = $contents ? $this->service->getDataRecord($contents) : [];
            logging(['date'=>$req_date, 'data-count'=>count($datas)], $this->service_name."\t $connection_type \t"."difference-settlement-response (O)");
            return $datas;    
        }
        catch(\Throwable $e)
        {
            error(['date'=>$req_date, 'datas'=>[], 'error' => $e->getMessage()], $this->service_name."\t $connection_type \t"."difference-settlement-response (X)");
            return [];
        }
    }

    public function setStartRecord($req_date)
    {
        if($this->service_name == 'galaxiamoneytree')
        {
            $record_type    = $this->setAtypeField('HD', 2);
            $create_dt      = $this->setAtypeField(date('Ymd'), 8);
            $rep_mid        = $this->setAtypeField($this->brand['rep_mid'], 20);
            $filter         = $this->setAtypeField('', $this->RQ_HEADER_FILTER_SIZE);
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
        if($this->service_name == 'galaxiamoneytree')
        {
            $record_type    = $this->setAtypeField('TR', 2);
            $total_count    = $this->setNtypeField($total_count, 7);
            $total_amount   = $this->setNtypeField($total_amount, 18);
            $filter         = $this->setAtypeField('', $this->RQ_TOTAL_FILTER_SIZE);
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
        try
        {
            if($this->main_connection_stat && $this->main_sftp_connection->exists($res_path))
                $contents = $this->main_sftp_connection->get($res_path);
            else if($this->dr_connection_stat && $this->dr_sftp_connection->exists($res_path))
                $contents = $this->dr_sftp_connection->get($res_path);
            else
                $contents = null;

            $datas = $contents ? $this->service->registerResponse($contents) : [];
            logging(['date'=>$req_date, 'datas'=>$datas, 'contents'=>$contents], $this->service_name.'-sub-business-registration-response');
            return $datas;
        }
        catch(\Throwable $e)
        {
            logging(['date'=>$req_date, 'datas'=>[]], $this->service_name.'-sub-business-registration-response('. $e->getMessage().")");
            return [];
        }
    }
}
