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
                $before_date = Carbon::now()->subMonths(1)->format('Y-m');
                if(isset($datas[$before_date]))
                {
                    $getIncreaseRate = function($col, $current, $before) {
                        return (($current[$col] - $before[$col])/$before[$col]) * 100;
                    };
                    $datas[$month->month]['amount_rate'] = $getIncreaseRate('amount', $datas[$month->month], $datas[$before_date]);
                    $datas[$month->month]['profit_rate'] = $getIncreaseRate('profit', $datas[$month->month], $datas[$before_date]);    
                }
                else
                {
                    $datas[$month->month]['amount_rate'] = 0;
                    $datas[$month->month]['profit_rate'] = 0;
                }
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
            }
        }
        return $this->response(0, $datas);
    }

    public function getUpSideChartFormat($query)
    {
        $datas = [];  
        $monthly = $query
            ->select(
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as month"),
                DB::raw("SUM(is_delete = 0) as add_count"),
                DB::raw("SUM(is_delete = 1) as del_count"),
            )
            ->where('updated_at', '>=', now()->subMonths(4))
            ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%Y-%m')"))
            ->orderBy('month')
            ->get();
        $total = 0;
        foreach($monthly as $month)
        {
            $total += $month->add_count;
            $month_total = $month->add_count + $month->del_count;
            $datas[$month->month] = [
                'add_rate' => $month->add_count/$month_total,
                'del_rate' => $month->del_count/$month_total,
                'add_count' => (int)$month->add_count,
                'del_count' => (int)$month->del_count,
            ];
            if($month->month == Carbon::now()->format('Y-m'))
            {
                $getIncreaseRate = function($col, $current, $before) {
                    return (($current[$col] - $before[$col])/$before[$col]) * 100;
                };                
                $before_date = Carbon::now()->subMonths(1)->format('Y-m');
                if(isset($datas[$before_date]))
                {
                    $datas[$month->month]['increase_rate'] = $getIncreaseRate('add_count', $datas[$month->month], $datas[$before_date]);
                }
            }
        }
        $datas['total'] = $total;
        return $datas;
    }

    /*
     * 가맹점 증감률 분석(저번달 대비)
     */
    public function upSideMchtAnalysis(Request $request)
    {
        $query = Merchandise::where('brand_id', $request->user()->brand_id);
        $query = globalAuthFilter($query, $request);
        $chart = $this->getUpSideChartFormat($query);
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
        $chart = $this->getUpSideChartFormat($query);
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
