<?php

namespace App\Http\Traits\Settle\Difference\Hecto;
use App\Enums\DifferenceSettleHectoRecordType;

trait requestTrait
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
        $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::START->value, 2);
        $req_date       = $this->setNtypeField($req_date, 8);
        $brand_business_num = $this->setAtypeField($brand_business_num, 10);
        $pg_type        = $this->setAtypeField("SETTLEBANK", 10);
        $filter         = $this->setAtypeField('', 370);
        return $record_type.$req_date.$brand_business_num.$pg_type.$filter."\n";
    }

    private function setHeaderRecord($rep_mcht_id)
    {
        $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::HEADER->value, 2);
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
            $ori_trx_dt = $trans[$i]->trx_dt;
            $ori_trx_dt = date('Ymd', strtotime($ori_trx_dt));
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

            $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::DATA->value, 2);
            $appr_type      = $this->setAtypeField($appr_type, 1);
            $trx_dt         = $this->setNtypeField($trx_dt, 8);
            $brand_business_num = $this->setAtypeField($brand_business_num, 10);
            $business_num   = $this->setAtypeField($business_num, 10);
            $trx_id         = $this->setAtypeField($trx_id, 40);
            $installment    = $this->setNtypeField($installment, 2);
            $ord_num        = $this->setAtypeField($trans[$i]->ord_num, 100);
            $amount         = $this->setNtypeField($amount, 15);
            $ori_amount     = $this->setNtypeField($amount, 15);
            $add_field      = $this->setAtypeField($trans[$i]->mcht_id, 40);
            $filter         = $this->setAtypeField('', 149);

            $data_record = 
                $record_type.$appr_type.$trx_dt.$brand_business_num.$business_num.
                $trx_id.$installment.$ord_num.$amount.$ori_amount.$ori_trx_dt.
                $add_field.$filter;
            $data_records .= $data_record."\n";
        }
        return [$data_records, count($trans), $total_amount];
    }

    private function setTotalRecord($total_count, $total_amount)
    {
        $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::TOTAL->value, 2);
        $total_count    = $this->setNtypeField($total_count, 7);
        $total_amount   = $this->setAtypeField($total_amount, 18);
        $filter         = $this->setAtypeField('', 373);
        return $record_type.$total_count.$total_amount.$filter."\n";
    }

    private function setEndRecord($total_count)
    {
        $record_type    = $this->setAtypeField(DifferenceSettleHectoRecordType::END->value, 2);
        $total_count    = $this->setNtypeField($total_count, 7);
        $filter         = $this->setAtypeField('', 391);
        return $record_type.$total_count.$filter."\n";
    }
}
