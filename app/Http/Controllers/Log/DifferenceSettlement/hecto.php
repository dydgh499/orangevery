<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Traits\Log\DifferenceSettlement\Hecto\requestTrait;
use App\Http\Traits\Log\DifferenceSettlement\Hecto\responseTrait;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class hecto
{
    use requestTrait, responseTrait;
    public $rep_mcht_id;
    protected $main_sftp_connection, $dr_sftp_connection;
    protected $main_connection_stat, $dr_connection_stat;

    public function __construct($rep_mcht_id)
    {   //ezpg0001
        $this->rep_mcht_id = $rep_mcht_id;
        $main_config = [
            'driver' => 'sftp',
            'host' => env('HECTO_DIFFER_SETTLE_MAIN_HOST'),
            'port' => (int)env('HECTO_DIFFER_SETTLE_PORT'),
            'username' => $this->rep_mcht_id,
            'password' => $this->rep_mcht_id."!1234",
            'passive' => false,
        ];
        $dr_config = [
            'driver' => 'sftp',
            'host' => env('HECTO_DIFFER_SETTLE_DR_HOST'),
            'port' => (int)env('HECTO_DIFFER_SETTLE_PORT'),
            'username' => $this->rep_mcht_id,
            'password' => $this->rep_mcht_id."!1234",
            'passive' => false,            
        ];
        config(['filesystems.disks.different_settlement_main_hecto' => $main_config]);
        config(['filesystems.disks.different_settlement_dr_hecto' => $dr_config]);
        try
        {
            $this->main_sftp_connection = Storage::disk('different_settlement_main_hecto');
            $this->main_connection_stat = true;
        }
        catch(Exception $e)
        {
            $this->main_connection_stat = false;
            logging(['type'=>'main', 'message' => $e->getMessage()]);
        }
        try
        {
            $this->dr_sftp_connection = Storage::disk('different_settlement_dr_hecto');
            $this->dr_connection_stat = false;
        }
        catch(Exception $e)
        {
            logging(['type'=>'dr', 'message' => $e->getMessage()]);
            $this->dr_connection_stat = false;
        }
    }

    public function request(Carbon $date, $brand_business_num, $trans)
    {
        $req_date = $date->format('Ymd');
        $save_path = "/edi_req/ST_PRFT_REQ_".$req_date;

        $mcht_ids = $trans->pluck('mid')->unique()->all();

        $total_count = 0;
        $full_record = $this->setStartRecord($req_date, $brand_business_num);

        foreach($mcht_ids as $mcht_id)
        {
            $mcht_trans = $trans->filter(function ($tran) use ($mcht_id) {
                return $tran->mid == $mcht_id;
            })->values();
            if(count($mcht_trans) > 0)
            {
                $header = $this->setHeaderRecord($mcht_id);
                [$data_records, $count, $amount] = $this->setDataRecord($mcht_trans, $brand_business_num);
                $total  = $this->setTotalRecord($count, $amount);
    
                $full_record .= $header.$data_records.$total;
                $total_count += $count;    
            }
        }
        $full_record .= $this->setEndRecord($total_count);

        $result = false;
        if($this->main_connection_stat)
            $result = $this->main_sftp_connection->put($save_path, $full_record);
        if($this->dr_connection_stat)
            $result = $this->dr_sftp_connection->put($save_path, $full_record);

        
        logging(['result'=>$result, 'save_path'=>$save_path], 'hecto-difference-settlement-request');
    }

    public function response(Carbon $date)
    {
        $req_date = $date->copy()->format('Ymd');
        $res_path = "/edi_rsp/ST_PRFT_REQ_".$req_date;

        if($this->main_connection_stat && $this->main_sftp_connection->exists($res_path))
            $contents = $this->main_connection_stat->get($res_path);
        else if($this->dr_connection_stat && $this->dr_sftp_connection->exists($res_path))
            $contents = $this->dr_sftp_connection->get($res_path);
        else
            $contents = null;

        $datas = $contents ? $this->getDataRecord($contents) : [];
        logging(['datas'=>$datas], 'hecto-difference-settlement-response');
        return $datas;
    }
}
