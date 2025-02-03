<?php
namespace App\Http\Controllers\Manager\Transaction;
use Illuminate\Support\Facades\DB;
use App\Enums\DevSettleType;
use App\Http\Controllers\Manager\Service\BrandInfo;

class SettleAmountCalculator
{
    static private function setOperatorSettleAmount($tran, $dev_settle_type)
    {
        if($dev_settle_type == DevSettleType::DEDUCT_FEE->value)
        {
            $dev_profit = self::getSettleAmount(6, $tran, true);
            $brand_profit = self::getSettleAmount(7, $tran, true);

            $tran['dev_settle_amount']   = round($dev_profit);
            $tran['brand_settle_amount'] = round($brand_profit);
        }
        else
        {
            $dev_profit = 0;
            $brand_profit = self::getSettleAmount(6, $tran);
            
            if($dev_settle_type == DevSettleType::NOT_APPLY->value)
                $dev_profit = 0;
            else if($dev_settle_type == DevSettleType::HEAD_OFFICE_PROFIT->value)
                $dev_profit = $brand_profit * $tran['dev_fee'];
            else if($dev_settle_type == DevSettleType::TOTAL_SALES->value)
                $dev_profit = $tran['amount'] * $tran['dev_fee'];

            $tran['dev_settle_amount']   = round($dev_profit);
            $tran['brand_settle_amount'] = round($brand_profit - $dev_profit);
        }
        // 실시간 비용
        $tran['dev_realtime_settle_amount'] = round($tran['amount'] * $tran['dev_realtime_fee']);
        $tran['brand_settle_amount'] -= $tran['dev_realtime_settle_amount'];
        return $tran;
    }
    
    static private function getSettleAmount($idx, $tran, $is_deduct_fee_type=false)
    {
        if($is_deduct_fee_type)
        {
            $pks  = ['mcht_id', 'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id', 'ps_id', 'ps_id']; // dev_id는 없음
            $fees = ['mcht_fee', 'sales0_fee', 'sales1_fee', 'sales2_fee','sales3_fee', 'sales4_fee', 'sales5_fee', 'dev_fee', 'ps_fee'];
        }
        else
        {
            $pks  = ['mcht_id', 'sales0_id','sales1_id','sales2_id','sales3_id','sales4_id','sales5_id','ps_id'];
            $fees = ['mcht_fee', 'sales0_fee', 'sales1_fee', 'sales2_fee','sales3_fee', 'sales4_fee', 'sales5_fee', 'ps_fee'];
        }

        $dest_fee = $fees[$idx+1];
        $fee_count = count($fees)-1;
        for($i=$idx; $fee_count > -1; $i--)
        {
            if($tran[$pks[$i]])    // 하위 영업자 - 상위(나의) 영업자, 하위 영업자가 존재할 시, 존재안하면 더 하위로
                return $tran['amount'] * ($tran[$fees[$i]] - $tran[$dest_fee]);
        }
        return 0;
    }

    static private function setTaxAmount($settle_tax_type, $profit)
    {
        switch($settle_tax_type)
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
        return $profit;
    }

    static private function setOperatorSettleAmountV2($tran)
    {
        $tran['dev_settle_amount']   = 0;
        $tran["brand_settle_amount"] = $tran["ps_id"] ? round($tran['amount'] * $tran["ps_fee"]) : 0;
        return $tran;
    }

    static private function setSalesSettleAmountV2($tran, $saleses)
    {
        for($i=0; $i<6; $i++)
        {
            $key = 'sales'.$i;
            $tran[$key."_settle_amount"] = $tran[$key."_id"] ? round($tran['amount'] * $tran[$key."_fee"]) : 0;
        }
        return $tran;
    }

    static private function setSalesSettleAmount($tran, $saleses)
    {
        for($i=0; $i<6; $i++)
        {
            $key = 'sales'.$i;
            $sales_id   = $tran[$key."_id"];
            $profit     = 0;
            if($sales_id)
            {
                $profit = self::getSettleAmount($i, $tran);
                $idx    = array_search($sales_id, array_column($saleses, 'id'));
                if($idx !== false)
                {
                    $profit = self::setTaxAmount($saleses[$idx]['settle_tax_type'], $profit);
                }
            }
            $tran[$key."_settle_amount"] = round($profit);
        }
        return $tran;
    }

    static public function setSettleAmount($trans)
    {
        $getSalesforces = function($trans) {
            $sales_ids = collect($trans)->flatMap(function ($tran) {
                return [
                    $tran['sales0_id'], $tran['sales1_id'], $tran['sales2_id'],
                    $tran['sales3_id'], $tran['sales4_id'], $tran['sales5_id'],
                ];
            })->unique();

            if(count($sales_ids) > 0)
            {
                return json_decode(json_encode(DB::table('salesforces')
                    ->whereIn('id', $sales_ids)
                    ->get(['id', 'settle_tax_type'])), true);
            }
            else
                return [];
        };

        $saleses = $getSalesforces($trans);
        $brand = count($trans) ? BrandInfo::getBrandById($trans[0]['brand_id']) : null;

        foreach($trans as &$tran)
        {
            //가맹점 수수료 세팅
            $tran["mcht_settle_amount"] = round(($tran['amount'] - ($tran['amount'] * ($tran['mcht_fee'] + $tran['hold_fee']))) - $tran['mcht_settle_fee']);
            if($brand['pv_options']['paid']['fee_input_mode'])
            {
                $tran = self::setSalesSettleAmount($tran, $saleses);
                $tran = self::setOperatorSettleAmount($tran, $brand['dev_settle_type']);   
            }
            else
            {
                $tran = self::setSalesSettleAmountV2($tran, $saleses);
                $tran = self::setOperatorSettleAmountV2($tran);   
            }
        }
        return $trans;
    }

}
