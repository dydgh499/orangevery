<?php
    namespace App\Http\Controllers\Utils;
    use Carbon\Carbon;

    class ChartFormat
    {
        static public function default($data)
        {
            $division_by_delete = function($item) {
                return $item->is_delete == true;
            };
            $chart = [
                'this_week_add' => 0,
                'this_week_del' => 0,
                'this_month_add' => 0,
                'this_month_del' => 0,
                'total' => $data['total'],
            ];
            $first_dy_week = Carbon::now()->startOfWeek();
            $first_dy_month = Carbon::now()->startOfMonth();
    
            if(count($data['content']))
            {
                $this_week = $data['content']->filter(function ($item) use ($first_dy_week) {
                    return Carbon::parse($item->created_at)->greaterThanOrEqualTo($first_dy_week);
                })->values();
        
                $this_month = $data['content']->filter(function ($item) use ($first_dy_month) {
                    return Carbon::parse($item->created_at)->greaterThanOrEqualTo($first_dy_month);
                })->values();
        
                $chart['this_week_add'] = $this_week->filter(function ($item) use ($division_by_delete) {
                    return $division_by_delete($item) == false;
                })->values()->count();
                $chart['this_week_del'] = $this_week->filter(function ($item) use ($division_by_delete) {
                    return $division_by_delete($item) == true;
                })->values()->count();
                
                $chart['this_month_add'] = $this_month->filter(function ($item) use ($division_by_delete) {
                    return $division_by_delete($item) == false;
                })->values()->count();
            
                $chart['this_month_del'] = $this_month->filter(function ($item) use ($division_by_delete) {
                    return $division_by_delete($item) == true;
                })->values()->count();
        
            }
            return $chart;
        }

        static public function settle($data, $settle_amount)
        {
            $chart = [
                'appr' => [
                    'amount' => 0,
                    'count' => 0,
                    'profit' => 0,
                    'trx_amount' => 0,
                    'hold_amount' => 0,
                    'settle_fee' => 0,
                    'total_trx_amount' => 0
                ],
                'cxl' => [
                    'amount' => 0,
                    'count' => 0,
                    'profit' => 0,
                    'trx_amount' => 0,
                    'hold_amount' => 0,
                    'settle_fee' => 0,
                    'total_trx_amount' => 0
                ],
                'total' => [
                    'amount' => 0,
                    'count' => 0,
                    'profit' => 0,
                    'trx_amount' => 0,
                    'hold_amount' => 0,
                    'settle_fee' => 0,
                    'total_trx_amount' => 0    
                ]
            ];
    
            // 트랜잭션 유형별로 데이터를 분류하며, 동시에 필요한 합계를 계산합니다.
            foreach ($data as $transaction) 
            {
                $type = $transaction->is_cancel ? 'cxl' : 'appr';
                $chart[$type]['amount'] += $transaction->amount;
                $chart[$type]['count']++;
                $chart[$type]['profit'] += $transaction[$settle_amount];
                $chart[$type]['trx_amount'] += $transaction->trx_amount;
                $chart[$type]['hold_amount'] += $transaction->hold_amount;
                $chart[$type]['settle_fee'] += $transaction->mcht_settle_fee;
                $chart[$type]['total_trx_amount'] += $transaction->total_trx_amount;
            }
            // 전체 차트 값을 계산합니다.
            foreach ($chart['appr'] as $key => $value) 
            {
                $chart['total'][$key] = $chart['appr'][$key] + $chart['cxl'][$key];
            }
            return $chart;  
        }

        static public function transaction($chart)
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
            ];
        }
    }
