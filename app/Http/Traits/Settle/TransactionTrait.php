<?php

namespace App\Http\Traits\Settle;
use Illuminate\Support\Facades\DB;

trait TransactionTrait
{
    protected function getSettleAmount($idx, $tran)
    {
        if($idx == -1)
        {   // 가맹점
            $profit = $tran['amount'] - ($tran['amount'] * ($tran['mcht_fee'] + $tran['hold_fee']));
            return $profit - $tran['mcht_settle_fee'];
        }
        else
        {
            $pks  = ['mcht_id', 'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id','ps_id'];
            $fees = ['mcht_fee', 'sales0_fee', 'sales1_fee', 'sales2_fee','sales3_fee', 'sales4_fee', 'sales5_fee', 'ps_fee'];
            $dest_fee = $fees[$idx+1];
            $fee_count = count($fees)-1;
            for($i=$idx; $fee_count > -1; $i--)
            {
                if($tran[$pks[$i]])    // 하위 영업자 - 상위(나의) 영업자, 하위 영업자가 존재할 시, 존재안하면 더 하위로
                    return $tran['amount'] * ($tran[$fees[$i]] - $tran[$dest_fee]);
            }
            return 0;
        }
    }

    protected function getSalesforces($sales_ids)
    {
        if(count($sales_ids) > 0)
        {
            $saleses = json_decode(json_encode(DB::table('salesforces')
                ->whereIn('id', $sales_ids)
                ->get(['id', 'settle_tax_type'])), true);
        }
        else
            $saleses = [];
        return $saleses;
    }

    protected function setSettleAmount($trans)
    {
        $sales_ids = collect($trans)->flatMap(function ($tran) {
            return [
                $tran['sales0_id'], $tran['sales1_id'], $tran['sales2_id'],
                $tran['sales3_id'], $tran['sales4_id'], $tran['sales5_id'],
            ];
        })->unique();
        $saleses = $this->getSalesforces($sales_ids);
        foreach($trans as &$tran)
        {
            //가맹점 수수료 세팅
            $tran["mcht_settle_amount"] = round($this->getSettleAmount(-1, $tran));
            //영업점 수수료 세팅
            for($i=0; $i<6; $i++)
            {
                $key = 'sales'.$i;
                $sales_id   = $tran[$key."_id"];
                $profit     = 0;
                if($sales_id)
                {
                    $profit = $this->getSettleAmount($i, $tran);
                    $idx    = array_search($sales_id, array_column($saleses, 'id'));
                    if($idx !== false)
                    {
                        switch($saleses[$idx]['settle_tax_type'])
                        {
                            case 1:
                                $profit *= 0.967;
                                break;
                            case 2:
                                $profit *= 0.9;
                                break;
                            case 3:
                                $profit *= 0.9;
                                $profit *= 0.967;
                                break;
                        }
                    }
                }
                $tran[$key."_settle_amount"] = round($profit);
            }
            //개발사, 본사 수수료 세팅
            $brand_profit = $this->getSettleAmount(6, $tran);
            $dev_profit   = $brand_profit * $tran['dev_fee'];
            $tran['dev_settle_amount']   = round($dev_profit);
            $tran['brand_settle_amount'] = round($brand_profit - $dev_profit);
        }
        return $trans;
    }

    
    public function getTotalCols($settle_key, $group_key=null)
    {
        $cols = [
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount"),
            DB::raw("SUM(is_cancel = 0) AS appr_count"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount"),
            DB::raw("SUM(is_cancel = 1) AS cxl_count"),
            DB::raw("SUM($settle_key) AS profit"),
        ];
        if($group_key)
            array_push($cols, $group_key);
        return $cols;
    }
    
    public function setTransChartFormat($chart)
    {
        return [
            'appr'  => [
                'amount'=> (int)$chart->appr_amount,
                'count' => (int)$chart->appr_count,
            ],
            'cxl'   => [
                'amount'=> (int)$chart->cxl_amount,
                'count' => (int)$chart->cxl_count,
            ],
            'amount'    => $chart->appr_amount + $chart->cxl_amount,
            'count'     => $chart->appr_count + $chart->cxl_count,
            'profit'    => (int)$chart->profit,
        ];
    }

    public function getSettleCol($request)
    {
        if(isMerchandise($request))
        {
            $group_key = 'mcht_id';
            $settle_key = 'mcht_settle_amount';
        }
        else if(isSalesforce($request))
        {
            $idx = globalLevelByIndex($request->level);
            $group_key  = 'sales'.$idx.'_id';
            $settle_key = 'sales'.$idx.'_settle_amount';
        }
        else
        {
            $settle = $request->level == 50 ? 'dev' :'brand';
            $group_key = 'brand_id';
            $settle_key = $settle."_settle_amount";
        }
        return [$settle_key, $group_key];
    }
}
