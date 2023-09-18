<?php

namespace App\Http\Controllers\Manager\Settle\Difference;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class hecto
{
    private function setNtypeField($string, $length)
    {
        return sprintf("%0".$length."s", $string);
    }

    private function setAtypeField($string, $length)
    {    
        return sprintf("%-".$length."s", $string);
    }

    private function setStartRecord($req_date, $brand_business_num)
    {
        $brand_business_num = str_replace('-', '', $brand_business_num);
        $record_type    = $this->setAtypeField("01", 2);
        $req_date       = $this->setNtypeField($req_date, 8);
        $brand_business_num = $this->setAtypeField($brand_business_num, 10);
        $pg_type        = $this->setAtypeField("SETTLEBANK", 10);
        $filter         = $this->setAtypeField('', 370);
        return $record_type.$req_date.$brand_business_num.$pg_type.$filter."\n";
    }

    private function setHeaderRecord($rep_mcht_id)
    {
        $record_type    = $this->setAtypeField("10", 2);
        $rep_mcht_id            = $this->setAtypeField($rep_mcht_id, 10);
        $filter         = $this->setAtypeField('', 388);
        return $record_type.$rep_mcht_id.$filter."\n";
    }

    private function setDataRecord($trans, $brand_business_num)
    {
        $brand_business_num = str_replace('-', '', $brand_business_num);
        $data_records = '';
        $total_amount = 0;
        for ($i=0; $i < count($trans); $i++) 
        { 
            $total_amount += $trans[$i]->amount;
            $business_num = str_replace('-', '', $trans[$i]->business_num);
            $appr_type  = $trans[$i]->is_cancel ? "1" : "0";
            $trx_dt     = $trans[$i]->is_cancel ? $trans[$i]->cxl_dt : $trans[$i]->trx_dt;
            $trx_id     = $trans[$i]->is_cancel ? $trans[$i]->ori_trx_id : $trans[$i]->trx_id;
            $trx_dt     = date('Ymd', strtotime($trx_dt));
            // install
            if($trans[$i]->is_cancel)
            {
                $installment = $trans[$i]->installment != 0 ? $trans[$i]->installment : 1;
                $installment = (string)$installment;
            }
            else
                $installment = '0';
            // amount
            $amount = abs($trans[$i]->amount);

            $record_type    = $this->setAtypeField("11", 2);
            $appr_type      = $this->setAtypeField($appr_type, 1);
            $trx_dt         = $this->setNtypeField($trx_dt, 8);
            $brand_business_num = $this->setAtypeField($brand_business_num, 10);
            $business_num   = $this->setAtypeField($business_num, 10);
            $trx_id         = $this->setAtypeField($trx_id, 40);
            $installment    = $this->setNtypeField($installment, 2);
            $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 100);
            $amount         = $this->setNtypeField($amount, 15);
            $ori_amount     = $this->setNtypeField($amount, 15);
            $appr_num       = $this->setAtypeField($trans[$i]->appr_num, 8);
            $add_field      = $this->setAtypeField('', 40);
            $filter         = $this->setAtypeField('', 149);

            $data_record = $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.$trx_id.$installment.$ord_num.$amount.$ori_amount.$appr_num.$add_field.$filter;
            $data_records .= $data_record."\n";
        }
        return [$data_records, count($trans), $total_amount];
    }

    private function setTotalRecord($total_count, $total_amount)
    {
        $record_type    = $this->setAtypeField("12", 2);
        $total_count    = $this->setNtypeField($total_count, 7);
        $total_amount   = $this->setAtypeField($total_amount, 18);
        $filter         = $this->setAtypeField('', 373);
        return $record_type.$total_count.$total_amount.$filter;        
    }

    private function setEndRecord($total_count)
    {
        $record_type    = $this->setAtypeField("12", 2);
        $total_count    = $this->setNtypeField($total_count, 7);
        $filter         = $this->setAtypeField('', 391);
        return $record_type.$total_count.$filter."\n";
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

    public function response($request)
    {
        
    }
}
