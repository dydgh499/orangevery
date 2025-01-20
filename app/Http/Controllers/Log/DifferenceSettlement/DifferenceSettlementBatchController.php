<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\DifferenceSettlementBatch;
use Carbon\Carbon;

class DifferenceSettlementBatchController extends DifferenceSettlementBatch
{
    public function differenceSettleRequest()
    {
        $brands     = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');
        logging([], "difference-settlement-request (".$yesterday.")");

        foreach($brands as $brand) 
        {
            $ids = $this->getRequestTransIds($brand, $yesterday, $yesterday);
            $this->differenceSettlementRequestProcess($brand, $date, $ids);
        }
    }

    public function differenceSettleResponse()
    {
        $brands = $this->getUseDifferentSettlementBrands();
        $date   = Carbon::now();
        logging([], "difference-settlement-response (".$date->format('Y-m-d').")");

        foreach($brands as $brand)
        {
            $this->differenceSettlementResponseProcess($brand, $date);
        }
    }

    /*
    * 차액정산 가맹점 정보 등록
    */
    public function merchandiseRegistrationRequest()
    {
        $brands     = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        logging([], "merchandise-registration-request (".$date->format('Y-m-d').")");
        foreach($brands as $brand) 
        {
            $pg = $this->getPGClass($brand);
            if($pg)
            {
                $mchts = $this->getMerchandiseRegistration($brand->pg_type, '51');
                $groups = $pg->setRegistrationDataRecord($date, $mchts);
                $this->updateReqeustMerchandiseRegistration($groups);
            }
        }
    }

    /*
    * 차액정산 가맹점 정보 등록 결과
    */
    public function merchandiseRegistrationResponse()
    {
        $brands     = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        logging([], "merchandise-registration-response (".$date->format('Y-m-d').")");
        foreach($brands as $brand) 
        {
            $pg = $this->getPGClass($brand);
            if($pg)
            {
                $groups  = $pg->getRegistrationDataRecord($date);
                $this->updateReqeustMerchandiseRegistration($groups);
            }
        }
    }

    /*
    * 차액정산 테스트 업로드
    */
    static public function differenceSettleRequestTest($ds_ids, $start_days, $end_days)
    {
        //DifferenceSettlementBatchController::differenceSettleRequestTest([1,2], 50, 1)
        $inst       = new DifferenceSettlementBatchController();
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');
        $start_day  = $date->copy()->subDay($start_days)->format('Y-m-d');
        $end_day    = $date->copy()->subDay($end_days)->format('Y-m-d');
        logging(['ds_ids' => $ds_ids], "difference-settlement-request-test ($start_day~$end_day, $yesterday)");

        $brands     = $inst->getUseDifferentSettlementBrands();
        foreach($brands as $brand)
        {
            if(in_array($brand->id, $ds_ids))
            {
                $ids = $inst->getNotApplyTransactionIds($brand, $yesterday, $start_day, $end_day);
                $inst->differenceSettlementRequestProcess($brand, $date, $ids);
            }
        }
    }

    static public function differenceSettleResponseTest($ds_id, $sub_day)
    {
        $inst   = new DifferenceSettlementBatchController();
        $date   = Carbon::now()->subDay($sub_day);
        logging(['ds_id' => $ds_id], "difference-settlement-response-test (".$date->format('Y-m-d').")");
        $brands = $inst->getUseDifferentSettlementBrands();
        foreach($brands as $brand)
        {
            if($brand->id === $ds_id)
            {
                $inst->differenceSettlementResponseProcess($brand, $date);
            }
        }
    }
}
