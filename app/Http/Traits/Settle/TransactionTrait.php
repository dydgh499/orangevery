<?php

namespace App\Http\Traits\Settle;
use Illuminate\Support\Facades\DB;
use App\Enums\DevSettleType;
use App\Models\Log\NotiSendHistory;
use Carbon\Carbon;

trait TransactionTrait
{
    protected function getSettleAmount($idx, $tran, $is_deduct_fee_type=false)
    {
        if($idx == -1)
        {   // 가맹점
            $profit = $tran['amount'] - ($tran['amount'] * ($tran['mcht_fee'] + $tran['hold_fee']));
            return $profit - $tran['mcht_settle_fee'];
        }
        else
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

    protected function setSalesSettleAmount($tran, $saleses)
    {
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
        return $tran;
    }

    protected function setOperatorSettleAmount($tran, $dev_settle_type)
    {
        if($dev_settle_type == DevSettleType::DEDUCT_FEE->value)
        {
            $dev_profit = $this->getSettleAmount(6, $tran, true);
            $brand_profit = $this->getSettleAmount(7, $tran, true);

            $tran['dev_settle_amount']   = round($dev_profit);
            $tran['brand_settle_amount'] = round($brand_profit);
        }
        else
        {
            $dev_profit = 0;
            $brand_profit = $this->getSettleAmount(6, $tran);
            
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

    public function setSettleAmount($trans, $dev_settle_type)
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
            $tran = $this->setSalesSettleAmount($tran, $saleses);
            //개발사, 본사 수수료 세팅
            $tran = $this->setOperatorSettleAmount($tran, $dev_settle_type);
        }
        return $trans;
    }

    
    public function getTotalCols($settle_amount, $group_key=null)
    {
        if($settle_amount === 'dev_settle_amount')
            $profit  = "(SUM($settle_amount) + SUM(dev_realtime_settle_amount)) AS profit";
        else
            $profit = "SUM($settle_amount) AS profit";

        $cols = [
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount"),
            DB::raw("SUM(is_cancel = 0) AS appr_count"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount"),
            DB::raw("SUM(is_cancel = 1) AS cxl_count"),
            DB::raw($profit),
        ];
        if($group_key)
            array_push($cols, $group_key);
        return $cols;
    }
    
    public function setTransChartFormat($chart)
    {
        return [
            'appr'  => [
                'amount'=> $chart ? (int)$chart->appr_amount : 0,
                'count' => $chart ? (int)$chart->appr_count: 0,
            ],
            'cxl'   => [
                'amount'=> $chart ? (int)$chart->cxl_amount : 0,
                'count' => $chart ? (int)$chart->cxl_count : 0,
            ],
            'amount'    => $chart ? $chart->appr_amount + $chart->cxl_amount : 0,
            'count'     => $chart ? $chart->appr_count + $chart->cxl_count : 0,
            'profit'    => $chart ? (int)$chart->profit : 0,
        ];
    }

    public function getNotiSendFormat($tran, $temp='')
    {
        return [
            'mid'   => $tran->mid,
            'tid'    => $tran->tid,
            'trx_id'    => $tran->trx_id,
            'amount'    => $tran->amount,
            'ord_num'   => $tran->ord_num,
            'appr_num'  => $tran->appr_num,
            'item_name' => $tran->item_name,
            'buyer_name'    => $tran->buyer_name,
            'buyer_phone'   => $tran->buyer_phone,
            'acquirer'      => $tran->acquirer,
            'issuer'        => $tran->issuer,
            'card_num'      => $tran->card_num,
            'installment'   => $tran->installment,
            'trx_dttm'      => $tran->trx_dt." ".$tran->trx_tm,
            'is_cancel'     => $tran->is_cancel,
            'temp'          => $temp,
        ];
    }

    public function notiSender($url, $tran, $temp='')
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept' => 'application/json',
        ];
        $params = $this->getNotiSendFormat($tran, $temp);
        if($tran->is_cancel)
        {
            $params['amount'] *= -1;
            $params['cxl_dttm'] = $tran->cxl_dttm;
            $params['ori_trx_id'] = $tran->ori_trx_id;
        }
        return post($url, $params, $headers);
    }

    public function save($res, $noti)
    {
        $body = json_encode($res['body']);
        $log = [
            'http_code' => $res['code'],
            'message'   => $body ? $body : $res['body'],
            'send_url'  => $noti->send_url,
            'trans_id'  => $noti->id,
            'brand_id'  => $noti->brand_id,
            'retry_count' => $noti->retry_count+1,
        ];
        return NotiSendHistory::create($log);        
    }

    function getSettleDate($trx_dt, $add_days, $pg_settle_type, $str_holidays) 
    {
        $currDate = Carbon::parse($trx_dt);
        $holidays = explode(',', $str_holidays); // 공휴일 문자열을 배열로 변환
    
        if ($pg_settle_type === 1) 
        {
            $counter = 0;
            while ($counter < $add_days) 
            {
                $currDate->addDay();
                // 주말이 아니고, 공휴일에 포함되지 않는 경우에만 카운터 증가
                if (!$currDate->isWeekend() && (empty($holidays) || !in_array($currDate->format('Y-m-d'), $holidays))) 
                    $counter++;
            }
        }
        else
            $currDate->addDays($add_days);
    
        return $currDate->format('Ymd');
    }

    function transDateFilter($request, $query)
    {
        if($request->s_dt && $request->e_dt)
        {   // 영업점, 가맹점 정산이력 추출이 아닐때
            $s_dt = strlen($request->s_dt) === 10 ? date($request->s_dt." 00:00:00") : $request->s_dt;
            $e_dt = strlen($request->e_dt) === 10 ? date($request->e_dt." 23:59:59") : $request->e_dt;
            return $query
                ->whereRaw("transactions.trx_at >= ?", [$s_dt])
                ->whereRaw("transactions.trx_at <= ?", [$e_dt]);    
        }
        else
            return $query;
    }
}
