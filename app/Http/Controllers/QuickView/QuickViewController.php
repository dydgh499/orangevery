<?php

namespace App\Http\Controllers\QuickView;

use App\Models\Transaction;

use App\Models\Merchandise;
use App\Models\Merchandise\PaymentModule;
use App\Models\CollectWithdraw;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @group Quick-View API
 *
 * 간편보기 API 입니다.
 */
class QuickViewController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
    }

    private function get3MonthlyTransactions($request)
    {
        $one_month_ago = Carbon::now()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d 00:00:00');
        $curl_date = Carbon::now()->format('Y-m-d 23:59:59');

        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
        $cols = TransactionFilter::getTotalCols($target_settle_amount);

        array_push($cols, DB::raw("DATE_FORMAT(trx_at, '%Y-%m-%d') as day"));

        return $this->transactions
            ->where('brand_id', $request->user()->brand_id)
            ->where('trx_at', '>=', $one_month_ago)
            ->where('trx_at', '<=', $curl_date)
            ->where($target_id, $request->user()->id)
            ->groupby(DB::raw("DATE_FORMAT(trx_at, '%Y-%m-%d')"))
            ->orderBy('day', 'desc')
            ->get($cols);
    }

    private function getMchtRankTransactions($request)
    {
        $one_month_ago = Carbon::now()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d 00:00:00');
        $curl_date = Carbon::now()->format('Y-m-d 23:59:59');

        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);

        $cols = TransactionFilter::getTotalCols($target_settle_amount, 'transactions.mcht_id');
        array_push($cols, 'merchandises.mcht_name');

        return $this->transactions
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->user()->brand_id)
            ->where('transactions.trx_at', '>=', $one_month_ago)
            ->where('transactions.trx_at', '<=', $curl_date)
            ->where('transactions.'.$target_id, $request->user()->id)
            ->groupby('transactions.mcht_id')
            ->orderBy('appr_amount', 'desc')
            ->get($cols);
    }

    private function groupedByMonth($contents)
    {
        $transactions = [];
        $now = Carbon::now();
        $last_month = $now->copy()->subMonthNoOverflow(1);
        
        $str_now = $now->format('Y-m-d');
        $str_cur_month = $now->format('Y-m');
        $str_last_month = $last_month->format('Y-m');
        
        $group = [
            $str_now => [
                "appr_amount" => 0,
                "appr_count" => 0,
                "cxl_amount" => 0,
                "cxl_count" => 0,
                "profit" => 0,
            ],
            $str_cur_month => [
                "appr_amount" => 0,
                "appr_count" => 0,
                "cxl_amount" => 0,
                "cxl_count" => 0,
                "profit" => 0,
            ],
            $str_last_month => [
                "appr_amount" => 0,
                "appr_count" => 0,
                "cxl_amount" => 0,
                "cxl_count" => 0,
                "profit" => 0,
            ],
        ];
        $addAmount = function($group, $content) {
            $group['appr_amount'] += $content->appr_amount;
            $group['appr_count'] += $content->appr_count;
            $group['cxl_amount'] += $content->cxl_amount;
            $group['cxl_count'] += $content->cxl_count;
            $group['profit'] += $content->profit;
            return $group;
        };
        foreach($contents as $content) 
        {
            $day = Carbon::parse($content->day);
            if ($day->isToday()) 
                $group[$str_now] = $addAmount($group[$str_now], $content);
            if ($day->year === $now->year && $day->month === $now->month) 
                $group[$str_cur_month] = $addAmount($group[$str_cur_month], $content);
            if ($day->year === $last_month->year && $day->month === $last_month->month) 
                $group[$str_last_month] = $addAmount($group[$str_last_month], $content);
        }
        return $group;
    }

    public function index(Request $request)
    { 
        $daily = $this->get3MonthlyTransactions($request);
        $mchts = $this->getMchtRankTransactions($request);
        $toIntFormat = function($day) {
            $day->appr_amount = (int)$day->appr_amount;
            $day->appr_count = (int)$day->appr_count;
            $day->cxl_amount = (int)$day->cxl_amount;
            $day->cxl_count = (int)$day->cxl_count;
            $day->profit = (int)$day->profit;
            return $day;
        };
        foreach($daily as &$day)
        {
            $day = $toIntFormat($day);
        }
        foreach($mchts as &$mcht)
        {
            $mcht = $toIntFormat($mcht);
        }
        $monthly = $this->groupedByMonth($daily);
        $group = [
            'daily' => $daily,
            'monthly' => $monthly,
            'mchts' => $mchts,
        ];
        return $this->response(0, $group);
    }
}
