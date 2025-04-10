<?php

namespace App\Http\Controllers\Manager\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Operator;
use App\Models\Gmid;

use App\Models\Log\DangerTransaction;
use App\Models\Log\ActivityHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Manager\Dashboard\TransactionDashboard;
use App\Http\Controllers\Ablilty\ActivityHistoryViewer;
use App\Http\Controllers\Ablilty\Ablilty;


/**
 * @group Dashboard API
 *
 * 대쉬보드 API입니다.
 */
class DashboardController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    /*
     * 월별 거래 분석(10개월)
     */
    public function monthlyTranAnalysis(Request $request)
    {
        $brand_id = $request->user()->brand_id;
        $level = Ablilty::isOperator($request) ? 35 : $request->user()->level;
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($level);
        $datas = [
            'monthly' => [],
            'weekly' => [],
            'cur_profit_rate' => 0,
            'cur_amount_rate' => 0,
            'cur_profit' => 0,
            'cur_amount' => 0,
        ];
        // set increase
        [$datas['cur_profit_rate'], $datas['cur_amount_rate'], $datas['cur_profit'], $datas['cur_amount']] = TransactionDashboard::getIncrease($brand_id, $target_settle_amount);
        // set daliy
        $datas['weekly'] = TransactionDashboard::getDaily($brand_id, $target_settle_amount);
        // set month
        $datas['monthly'] = TransactionDashboard::getMonthly($brand_id, $target_settle_amount);
        return $this->response(0, $datas);
    }
    
    public function increase($query, $table, $brand_id, $select, $cols)
    {
       return $query->selectRaw($select)
        ->fromSub(function ($query) use($table, $brand_id, $cols) {
            $cur_e_dt = Carbon::now()->format('Y-m-d');
            $cur_s_dt = Carbon::now()->startOfMonth()->format('Y-m-d');
            $last_e_dt = Carbon::now()->subMonthNoOverflow(1)->format('Y-m-d');
            $last_s_dt = Carbon::now()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');
            $format = "(SELECT $cols FROM $table WHERE brand_id=$brand_id AND date(updated_at) BETWEEN ? AND ?)";

            $query->from($table)
                ->selectRaw("$format as cur_mon_count, $format as last_month_count")
                ->setBindings([$cur_s_dt, $cur_e_dt, $last_s_dt, $last_e_dt]);
        }, 'counts')
        ->first();
    }

    public function getUpSideChartFormat($query, $orm, $table, $brand_id)
    {
        $select = '(counts.cur_mon_count - counts.last_month_count) / NULLIF(counts.last_month_count, 1) * 100 AS cur_increase_rate';
        $increase = $this->increase($orm, $table, $brand_id, $select, 'SUM(is_delete = 0)');        
        if($increase)
        {
            $datas = [
                'cur_increase_rate' => (float)$increase['cur_increase_rate'],
                'total' => (clone $query)->where('is_delete', 0)->count(),
                'graph' => [],
            ];
            $monthly = $query
                ->select(
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m') as month"),
                    DB::raw("SUM(is_delete = 0) as add_count"),
                    DB::raw("SUM(is_delete = 1) as del_count"),
                )
                ->where('updated_at', '>=', now()->subMonthNoOverflow(4))
                ->groupBy(DB::raw("DATE_FORMAT(updated_at, '%Y-%m')"))
                ->orderBy('month')
                ->get();
            foreach($monthly as $month)
            {
                //$total += $month->add_count;
                $month_total = $month->add_count + $month->del_count;
                $datas['graph'][$month->month] = [
                    'add_rate' => $month->add_count/$month_total,
                    'del_rate' => $month->del_count/$month_total,
                    'add_count' => (int)$month->add_count,
                    'del_count' => (int)$month->del_count,
                ];
            }
        }
        else
        {
            $datas = [
                'cur_increase_rate' => 0,
                'total' => 0,
                'graph' => [],
            ];
        }

        return $datas;
    }

    /*
     * 가맹점 증감률 분석(저번달 대비)
     */
    public function upSideMchtAnalysis(Request $request)
    {
        $query = Merchandise::where('brand_id', $request->user()->brand_id);
        $query = globalAuthFilter($query, $request);
        $chart = $this->getUpSideChartFormat($query, new Merchandise, 'merchandises', $request->user()->brand_id);   

        return $this->response(0, $chart);
    }

    /*
     * 영업라인인 증감률 분석(저번달 대비)
     */
    public function upSideSaleAnalysis(Request $request)
    {
        $query = Salesforce::where('brand_id', $request->user()->brand_id);
        if(Ablilty::isSalesforce($request))
            $query = $query->where('id', $request->user()->id);        
        $chart = $this->getUpSideChartFormat($query, new Salesforce, 'salesforces', $request->user()->brand_id);
        return $this->response(0, $chart);
    }

    public function getRecentDangerHistories(Request $request)
    {
        request()->merge([
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
        if(Ablilty::isOperator($request))
            return $this->response(0, ['content' => ActivityHistoryViewer::getRecentSelect($request)]);
        else
            return $this->response(0, ['content' => []]);
    }

    public function getLockedUsers(Request $request)
    {
        $brand_id = $request->user()->brand_id;
        $getUsers = function($orm, $brand_id, $type) {
            $cols = ['id', 'user_name', 'nick_name', 'phone_num', 'locked_at'];
            if($type === 'merchandise')
                $cols[] = DB::raw('10 as level');
            else if($type === 'gmid')
                $cols[] = DB::raw('11 as level');
            else
                $cols[] = 'level';

            return $orm
                ->where('brand_id', $brand_id)
                ->where('is_delete', false)
                ->where('is_lock', 1)
                ->select($cols);
        };
        if(Ablilty::isOperator($request))
        {
            $mcht = $getUsers(new Merchandise, $brand_id, 'merchandise');
            $sale = $getUsers(new Salesforce, $brand_id, 'salesforce');
            $oper = $getUsers(new Gmid, $brand_id, 'gmid');
            $oper = $getUsers(new Operator, $brand_id, 'operator');
            $query = $mcht->unionAll($sale)->unionAll($oper);    
            
            $content = $query->orderBy('level', 'desc')->orderBy('locked_at', 'desc')->get();
            return $this->response(0, ['content' => $content]);
        }
        else if(Ablilty::isSalesforce($request) && $request->user()->is_able_unlock_mcht)
        {
            $query = Merchandise::where('sales'.globalLevelByIndex($request->user()->level).'_id', $request->user()->id);
            $mcht = $getUsers($query, $brand_id, 'merchandise');
            $content = $mcht->orderBy('level', 'desc')->orderBy('locked_at', 'desc')->get();
            return $this->response(0, ['content' => $content]);
        }
        else
            return $this->response(0, ['content'=>[]]);
    }
}
