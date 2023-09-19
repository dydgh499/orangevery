<?php

namespace App\Http\Controllers\Manager\Settle\Difference;

use App\Http\Traits\Settle\Difference\Hecto\requestTrait;
use App\Http\Traits\Settle\Difference\Hecto\responseTrait;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class hecto
{
    use requestTrait, responseTrait;

    public function __construct()
    {

    }

    public function request(Carbon $date, $brand_business_num, $rep_mcht_id, $trans)
    {
        $req_date = $date->format('Ymd');
        $save_path = "/edi_req/ST_PRFT_REQ_".$req_date;

        $start  = $this->setStartRecord($req_date, $brand_business_num);
        $header = $this->setHeaderRecord($rep_mcht_id);
        [$data_records, $total_count, $total_amount] = $this->setDataRecord($trans, $brand_business_num);
        $total  = $this->setTotalRecord($total_count, $total_amount);
        $end    = $this->setEndRecord($total_count);

        $full_record = $start.$header.$data_records.$total.$end;
        try
        {
            $result = Storage::disk('different_settlement_main_hecto')->put($save_path, $full_record);
        }
        catch(Exception $e)
        {
            logging(['type'=>'main', 'message' => $e->getMessage()]);
        }
        try
        {
            $result = Storage::disk('different_settlement_dr_hecto')->put($save_path, $full_record);
        }
        catch(Exception $e)
        {
            logging(['type'=>'dr', 'message' => $e->getMessage()]);
        }
        logging(['result'=>$result, 'save_path'=>$save_path]);
    }

    public function response(Carbon $date, $rep_mcht_id)
    {
        $req_date = $date->copy()->format('Ymd');
        $res_path = "/edi_rsp/ST_PRFT_REQ_".$req_date;
        $contents = null;
        try
        {
            if(Storage::disk('different_settlement_main_hecto')->exists($res_path))
                $contents = Storage::disk('different_settlement_main_hecto')->get($res_path);
        }
        catch(Exception $e)
        {
            if(Storage::disk('different_settlement_dr_hecto')->exists($res_path))
                $contents = Storage::disk('different_settlement_dr_hecto')->get($res_path);
        }
        if($contents)
        {
            $save_date = $date->copy()->format('Y-m-d');
            $save_path = "/hecto/$rep_mcht_id/$save_date.json";
            $result = json_encode([
                'content'   => $this->getDataRecord($contents),
                'totals'    => $this->getTotalRecord($contents),
            ]);
            $contents = Storage::disk('local')->put($save_path, $result);
            return true;
        }
        else
            return false;
    }
}
