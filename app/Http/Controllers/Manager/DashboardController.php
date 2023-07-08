<?php

namespace App\Http\Controllers\Manager;

use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\PayModule;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    private function setWeeklyRate($charts, $week_trans, $last_week_trans)
    {
        $cur_month  = Carbon::now()->format('Y-m');
        $cur_chart = [];
        foreach($charts as $key => $value)
        {
            if($key == $cur_month)
                $cur_chart = $value;
        }
        if(count($cur_chart) > 0)
        {
            $week_chart = [];
            foreach($week_trans as $transaction) {
                $yoil = Carbon::parse($transaction->trx_dt)->dayOfWeek;
                if (!isset($week_chart[$yoil])) {
                    $week_chart[$yoil] = [];
                }
                $week_chart[$yoil][] = $transaction;
            }
            $charts[$cur_month]['week'] = [];
            $this_week_amount = 0;
            foreach ($week_chart as $key => $transactions) {            
                $charts[$cur_month]['week'][$key] = getDefaultTransChartFormat(collect($transactions));
                $this_week_amount += $charts[$cur_month]['week'][$key]['amount'];
            }

            ksort($charts[$cur_month]['week']);
            $last_week_chart = getDefaultTransChartFormat(collect($last_week_trans));
            if($last_week_chart['amount'])
                $charts[$cur_month]['week_amount_rate'] = (($this_week_amount - $last_week_chart['amount'])/($last_week_chart['amount'])/100);
            else
                $charts[$cur_month]['week_amount_rate'] = 0;
        }
        return $charts;
    }
    # 작월대비 거래비율 세팅
    private function setCurrentMonthRate($charts, $cur_trans, $last_trans)
    {
        $cur_month  = Carbon::now()->format('Y-m');
        $one_months_ago = Carbon::now()->subMonths(1)->format('Y-m');
        $cur_chart = [];
        $last_chart = [];
        foreach($charts as $key => $value)
        {
            if($key == $cur_month)
                $cur_chart = $value;
            if($key == $one_months_ago)
                $last_chart = $value;
        }
        if(isset($charts[$cur_month]->transactions) == false)
        {
            $charts[$cur_month] = getDefaultTransChartFormat(collect([]));
            $charts[$cur_month]['modules']['terminal_count'] = 0;
            $charts[$cur_month]['modules']['hand_count'] = 0;
            $charts[$cur_month]['modules']['auth_count'] = 0;
            $charts[$cur_month]['modules']['simple_count'] = 0;
            $charts[$cur_month]['week_amount_rate'] = 0;
            $cahrts[$cur_month]['week'] = [];
        }
        if(count($last_chart) > 0)
        {
            if($last_chart['amount'])
                $charts[$cur_month]['amount_rate'] = (($cur_chart['amount'] - $last_chart['amount'])/($last_chart['amount'])) * 100;
            else
                $charts[$cur_month]['amount_rate'] = 0;
            if($last_chart['profit'])
                $charts[$cur_month]['profit_rate'] = (($cur_chart['profit'] - $last_chart['profit'])/($last_chart['profit'])) * 100;    
            else
                $charts[$cur_month]['profit_rate'] = 0;
        }
        else
        {
            $charts[$cur_month]['amount_rate'] = 0;
            $charts[$cur_month]['profit_rate'] = 0;
        }
        return $charts;
    }

    private function setMonthlyPayModule($transactions)
    {
        return [
            'terminal_count' => $transactions->filter(function ($transaction) {
                return $transaction->module_type == 0;
            })->count(),
            'hand_count' => $transactions->filter(function ($transaction) {
                return $transaction->module_type == 1;
            })->count(),
            'auth_count' => $transactions->filter(function ($transaction) {
                return $transaction->module_type == 2;
            })->count(),
            'simple_count' => $transactions->filter(function ($transaction) {
                return $transaction->module_type == 3;
            })->count(),
        ];
    }
    /*
     * 월별 거래 분석(10개월)
     */
    public function monthlyTranAnalysis(Request $request)
    {
        $charts = [];
        $week_trans = [];
        $last_week_trans = [];
        $cur_trans = [];
        $last_trans = [];
        $month_trans = [];

        $cur_day    = Carbon::now();
        $cur_month  = $cur_day->copy()->startOfMonth();
        $ten_months_ago = $cur_month->copy()->subMonths(9)->startOfMonth()->format('Y-m-d');

        $query  = Transaction::where('brand_id', $request->user()->brand_id)
                ->where('trx_dt', '>=', $ten_months_ago)
                ->where('is_delete', false);
        $query = globalAuthFilter($query, $request);

        $query->chunk(1000, function ($transactions) use (&$month_trans, $cur_day, $cur_month, &$last_week_trans, &$week_trans, &$cur_trans, &$last_trans) {
            $next_month     = $cur_month->copy()->addMonth(1)->startOfMonth();
            $one_months_ago = $cur_month->copy()->subMonths(1)->startOfMonth();
            $six_days_ago   = Carbon::now()->subDays(6);
            $thit_days_ago  = Carbon::now()->subDays(13);
    
            foreach ($transactions as $transaction) {
                $month = Carbon::parse($transaction->trx_dt)->format('Y-m');
                $month_trans[$month][] = $transaction;
                $trx_dt = Carbon::parse($transaction->trx_dt);
                $cxl_dt = Carbon::parse($transaction->cxl_dt);

                $isCurTran = (!$transaction->is_cancel && $trx_dt->between($cur_month, $next_month, true)) ||
                            ($transaction->is_cancel && $cxl_dt->between($cur_month, $next_month, true));
                $isLastTran = (!$transaction->is_cancel && $trx_dt->between($one_months_ago, $cur_month, false)) ||
                            ($transaction->is_cancel && $cxl_dt->between($one_months_ago, $cur_month, false));
                $isSixDaysTrans = (!$transaction->is_cancel && $trx_dt->between($six_days_ago, $cur_day, true)) ||
                                ($transaction->is_cancel && $cxl_dt->between($six_days_ago, $cur_day, true));
                $isThirDaysTrans = (!$transaction->is_cancel && $trx_dt->between($thit_days_ago, $six_days_ago, true)) ||
                                ($transaction->is_cancel && $cxl_dt->between($six_days_ago, $six_days_ago, true));
    
                if ($isCurTran)
                    $cur_trans[] = $transaction;
                if ($isLastTran)
                    $last_trans[] = $transaction;
                if ($isSixDaysTrans)
                    $week_trans[] = $transaction;
                if ($isThirDaysTrans)
                    $last_week_trans[] = $transaction;             
            }
        });
        
        foreach ($month_trans as $key => $transactions) {            
            $charts[$key] = getDefaultTransChartFormat(collect($transactions));           
        }
        
        foreach ($month_trans as $key => $transactions) {           
            $charts[$key]['modules'] = $this->setMonthlyPayModule(collect($transactions));
        }
        $charts = $this->setWeeklyRate($charts, $week_trans, $last_week_trans);
        $charts = $this->setCurrentMonthRate($charts, $cur_trans, $last_trans);
        
        return $this->response(0, $charts);
    }

    public function getUpSideChartFormat($data)
    {        
        $division = function($item) {
            return $item->is_delete == true;
        };
        $cur_month  = Carbon::now()->startOfMonth();
        $next_month = $cur_month->copy()->addMonth(1)->startOfMonth();

        $chart = [];  
         $chart['total'] = $data->filter(function ($item) use($division) {
            return $division($item) === false;
        })->count();

        for ($i = 0; $i < 5; $i++) {
            $start_month = $cur_month->copy()->subMonths($i)->startOfMonth();
            $end_month = $next_month->copy()->subMonths($i)->startOfMonth();

            $items = $data->filter(function ($item) use ($start_month, $end_month) {
                $created_at = Carbon::parse($item->created_at);
                return $created_at->greaterThanOrEqualTo($start_month) && $created_at->lessThan($end_month);
            })->values();

            $add = $items->filter(function ($item) use($division) {
                return $division($item) === false;
            })->count();
            $del = $items->filter(function ($item) use($division) {
                return $division($item) === true;
            })->count();

            $chart['month'.$i] = [
                'add' => ($add/$chart['total']),
                'del' => ($del/$chart['total']),
            ];
        }       
        return $chart;
    }

    /*
     * 가맹점 증감률 분석(저번달 대비)
     */
    public function upSideMchtAnalysis(Request $request)
    {
        $query = Merchandise::where('brand_id', $request->user()->brand_id);
        $query = globalAuthFilter($query, $request);
        $mcht = $query->get(['id', 'is_delete','created_at']);

        $chart = $this->getUpSideChartFormat($mcht);

        return $this->response(0, $chart);
    }

    /*
     * 영업점 증감률 분석(저번달 대비)
     */
    public function upSideSaleAnalysis(Request $request)
    {
        $query = Salesforce::where('brand_id', $request->user()->brand_id);
        if(isSalesforce($request))
            $query = $query->where('id', $request->user()->id);
        $sales = $query->get(['id', 'is_delete','created_at']);

        $chart = $this->getUpSideChartFormat($sales);
        return $this->response(0, $chart);
    }

    /*
     * 결제모듈 사용량 분석(6개월) (장비, 수기, 인증, 간편)
     */
    public function usagePayModuleAnalysis(Request $request)
    {        
        return $this->response(0, []);
    }
}
