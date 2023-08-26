<?php

namespace App\Http\Controllers\Manager;

use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Log\DangerTransaction;
use App\Models\Log\OperatorHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    /*
     * 월별 거래 분석(10개월)
     */
    public function monthlyTranAnalysis(Request $request)
    {
        $datas = [];
        if(isOperator($request))
        {
            $key = 'brand_id';
            $id =  $request->user()->brand_id;
            $settle_key = 'brand_settle_amount';
        }
        else if(isSalesforce($request))
        {
            $idx = globalLevelByIndex($request->user()->level);
            $key = 'sales'.$idx.'_id';
            $id =  $request->user()->id;
            $settle_key = 'sales'.$idx.'_settle_amount';
        }
        $monthly = DB::select('CALL getMonthlyAmount(?, ?, ?)', [$key, $id, $settle_key]);
        $daily = DB::select('CALL getDailyAmount(?, ?)', [$key, $id]);
        foreach($monthly as $month)
        {
            $datas[$month->month] = [
                'modules' => [
                    "terminal_count" => (int)$month->terminal_count,
                    "hand_count"    => (int)$month->hand_count,
                    "auth_count"    => (int)$month->auth_count,
                    "simple_count"  => (int)$month->simple_count
                ],
                'appr'  => [
                    'amount'=> (int)$month->appr_amount,
                    'count' => (int)$month->appr_count,
                ],
                'cxl'   => [
                    'amount'=> (int)$month->cxl_amount,
                    'count' => (int)$month->cxl_count,
                ],
                'amount'    => $month->appr_amount + $month->cxl_amount,
                'count'     => $month->appr_count + $month->cxl_count,
                'profit'    => (int)$month->profit,
            ];
            if($month->month == Carbon::now()->format('Y-m'))
            {
                $datas[$month->month]['week'] = [];
                foreach($daily as $day)
                {
                    $datas[$month->month]['week'][$day->day] = [
                        'appr' => [
                            'amount' => (int)$day->appr_amount,
                        ],
                        'cxl' => [
                            'amount' => (int)$day->cxl_amount,
                        ],
                        'amount' => $day->appr_amount + $day->cxl_amount
                    ];
                }
                $getIncreaseRate = function($col, $current, $before) {
                    return (($current[$col] - $before[$col])/$before[$col]) * 100;
                };
                $before_date = Carbon::now()->subMonths(1)->format('Y-m');
                $datas[$month->month]['amount_rate'] = $getIncreaseRate('amount', $datas[$month->month], $datas[$before_date]);
                $datas[$month->month]['profit_rate'] = $getIncreaseRate('profit', $datas[$month->month], $datas[$before_date]);
            }
        }
        return $this->response(0, $datas);
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

            if($chart['total'])
            {
                $chart['month'.$i] = [
                    'add' => ($add/$chart['total']),
                    'del' => ($del/$chart['total']),
                ];    
            }
            else
            {
                $chart['month'.$i] = [
                    'add' => 0,
                    'del' => 0,
                ];
            }
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

    public function getRecentDangerHistories(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 9,
        ]);
        $cols = [
            'danger_transactions.*',
            'merchandises.mcht_name',
            'transactions.module_type',
            'transactions.issuer',
            'transactions.card_num',
            'transactions.installment',
            'transactions.amount',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
        ];
        $query = DangerTransaction::join('merchandises', 'danger_transactions.mcht_id', '=', 'merchandises.id')
            ->join('transactions', 'danger_transactions.trans_id', '=', 'transactions.id')
            ->where('danger_transactions.brand_id', $request->user()->brand_id);
        $query = globalAuthFilter($query, $request, 'transactions');
        $data = $this->getIndexData($request, $query, 'danger_transactions.id', $cols, 'transactions.created_at');
        return $this->response(0, $data);
    }

    public function getRecentOperatorHistories(Request $request)
    {   
        if(isOperator($request))
        {
            $request->merge([
                'page' => 1,
                'page_size' => 5,
            ]);
            $operator_histories = new OperatorHistory();
            $query = $operator_histories
                ->join('operators', 'operator_histories.oper_id', '=', 'operators.id')
                ->where('operator_histories.brand_id', $request->user()->brand_id);
            $data = $this->getIndexData($request, $query, 'operator_histories.id', $operator_histories->cols, 'operator_histories.created_at');
            return $this->response(0, $data);
        }
        else
            return $this->response(0, ['content'=>[]]);
    }
}
