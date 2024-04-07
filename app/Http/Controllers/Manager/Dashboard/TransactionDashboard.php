<?php
namespace App\Http\Controllers\Manager\Dashboard;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class TransactionDashboard
{
    static public function getMonthly($brand_id, $target_settle_amount)
    {
        $monthly = [];
        $transaction_counts = self::getMonthTransactionCounts($brand_id);
        for ($i=0; $i < 10; $i++) 
        {
            $trx_carbon = Carbon::now()->subMonthNoOverflow($i);
            $trx_dt = $trx_carbon->copy()->format('Y-m-1');
            $trx_month = $trx_carbon->copy()->format('Y-m');

            $month  = self::getMonthTransactionsByRedis($brand_id, $trx_dt, $target_settle_amount, $transaction_counts[$i]);
            $monthly[$trx_month] = self::getResponseData($month);
        }
        return array_reverse($monthly);
    }

    static public function getDaily($brand_id, $target_settle_amount)
    {
        $weekly = [];
        $s_dt = Carbon::now()->subDay(6)->format('Y-m-d 00:00:00');
        $e_dt = Carbon::now()->format('Y-m-d 23:59:59');
        
        $query = Transaction::where('brand_id', $brand_id)
            ->where('trx_at', '>=', $s_dt)
            ->where('trx_at', '<=', $e_dt);
        $query = self::ifSalesLevel($query);
        $daily = $query
            ->groupByRaw("DATE_FORMAT(trx_at, '%Y-%m-%d')")
            ->orderBy("day")
            ->selectRaw("
                DATE_FORMAT(trx_at, '%Y-%m-%d') AS day,
                SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount,
                SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount,
                SUM($target_settle_amount) AS profit"
            )->get();
        foreach($daily as $day)
        {
            $weekly[$day->day] = [
                'appr' => [
                    'amount' => (int)$day->appr_amount,
                ],
                'cxl' => [
                    'amount' => (int)$day->cxl_amount,
                ],
                'amount' => $day->appr_amount + $day->cxl_amount,
                'profit' => (int)$day->profit,
            ];
        }
        return $weekly;
    }

    static public function getIncrease($brand_id)
    {
        $cur_e_dt = Carbon::now()->format('Y-m-d');
        $cur_s_dt = Carbon::now()->startOfMonth()->format('Y-m-d');
        $last_e_dt = Carbon::now()->subMonthNoOverflow(1)->format('Y-m-d');
        $last_s_dt = Carbon::now()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');

        $current_month_range = "(trx_at >= '$cur_s_dt' AND trx_at <= '$cur_e_dt')";
        $last_month_range = "(trx_at >= '$last_s_dt' AND trx_at <= '$last_e_dt')";

        $query = Transaction::selectRaw("
            SUM(CASE WHEN $current_month_range THEN brand_settle_amount ELSE 0 END) AS cur_profit,
            SUM(CASE WHEN $current_month_range THEN amount ELSE 0 END) AS cur_amount,
            SUM(CASE WHEN $last_month_range THEN brand_settle_amount ELSE 0 END) AS last_profit,
            SUM(CASE WHEN $last_month_range THEN amount ELSE 0 END) AS last_amount")
            ->where('brand_id', $brand_id)
            ->where(function ($query) use ($current_month_range, $last_month_range) {
                return $query->where(function ($query) use ($current_month_range) {
                    return $query->whereRaw($current_month_range);
                })->orWhere(function ($query) use ($last_month_range) {
                    return $query->whereRaw($last_month_range);
                });
            });
        $query = self::ifSalesLevel($query);

        $info = $query->first();

        $cur_profit_rate = $info->last_profit ? Round(($info->cur_profit - $info->last_profit) / $info->last_profit * 100, 3) : 0;
        $cur_amount_rate = $info->last_amount ? Round(($info->cur_amount - $info->last_amount) / $info->last_amount * 100, 3) : 0;
        return [$cur_profit_rate, $cur_amount_rate, (int)$info->cur_profit, (int)$info->cur_amount];
    }

    
    static public function getMonthTransactionCounts($brand_id)
    {
        $base_query = [];
        $getMonthTransactionCountIntance = function($brand_id, $s_dt, $e_dt) {
            $query = Transaction::where('brand_id', $brand_id)
                ->where('trx_at', '>=', $s_dt)
                ->where('trx_at', '<=', $e_dt);
            $query = self::ifSalesLevel($query);
            return $query->selectRaw("COUNT(*) AS total_count")->toBase();
        };
        for ($i=0; $i < 10; $i++) 
        {
            $trx_dt = Carbon::now()->subMonthNoOverflow($i)->format('Y-m-1');
            $s_dt = Carbon::createFromFormat('Y-m-d', $trx_dt)->startOfMonth()->format('Y-m-d 00:00:00');
            $e_dt = Carbon::createFromFormat('Y-m-d', $trx_dt)->endOfMonth()->format('Y-m-d 23:59:59');
            $transaction_query = $getMonthTransactionCountIntance($brand_id, $s_dt, $e_dt);
            if($i === 0)
                $base_query = $transaction_query;
            else
                $base_query = $base_query->unionAll($transaction_query);
        }
        $transaction_counts = $base_query->get();
        return json_decode(json_encode($transaction_counts), true);
    }

    static public function getMonthTransactionsByDB($brand_id, $trx_dt, $target_settle_amount)
    {
        $s_dt = Carbon::createFromFormat('Y-m-d', $trx_dt)->startOfMonth()->format('Y-m-d 00:00:00');
        $e_dt = Carbon::createFromFormat('Y-m-d', $trx_dt)->endOfMonth()->format('Y-m-d 23:59:59');
        $query = Transaction::where('brand_id', $brand_id)
            ->where('trx_at', '>=', $s_dt)
            ->where('trx_at', '<=', $e_dt);
        $query = self::ifSalesLevel($query);

        return $query->selectRaw("
                COUNT(*) AS total_count,
                SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount,
                SUM(is_cancel = 0) AS appr_count,
                SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount,
                SUM(is_cancel = 1) AS cxl_count,
                SUM($target_settle_amount) AS profit,
                SUM(module_type = 0) AS terminal_count,
                SUM(module_type = 1) AS hand_count,
                SUM(module_type = 2) AS auth_count,
                SUM(module_type = 3) AS simple_count
            ")
            ->first()
            ->makeHidden(['trx_amount', 'hold_amount', 'trx_dttm', 'cxl_dttm', 'total_trx_amount'])
            ->toArray();
    }

    static public function setMonthTransactionsByRedis($key_name, $brand_id, $trx_dt, $target_settle_amount)
    {
        $MONTH_TIME = 2678400;        
        $transactions = self::getMonthTransactionsByDB($brand_id, $trx_dt, $target_settle_amount);
        Redis::set($key_name, json_encode($transactions), 'EX', $MONTH_TIME);
        return $transactions;
    }

    static public function getMonthTransactionsByRedis($brand_id, $trx_dt, $target_settle_amount, $transaction_count)
    {
        $key_name = 'dashboards-transactions-month-'.$brand_id.'-'.$trx_dt.'-'.$target_settle_amount;
        $redis_trans = Redis::get($key_name);
        if($redis_trans === null)
            return self::setMonthTransactionsByRedis($key_name, $brand_id, $trx_dt, $target_settle_amount);
        else
        {
            $redis_trans = json_decode($redis_trans, true);
            if((int)$transaction_count['total_count'] === (int)$redis_trans['total_count'])
                return $redis_trans;
            else
                return self::setMonthTransactionsByRedis($key_name, $brand_id, $trx_dt, $target_settle_amount);
        }
    }

    static public function ifSalesLevel($query)
    {
        if(isSalesforce(request()))
        {
            $id = request()->user()->id;
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo(request()->user()->level);
            $query = $query->where($target_id, $id);
        }
        return $query;
    }

    static public function getResponseData($month)
    {
        return [
            'modules' => [
                "terminal_count" => $month ? (int)$month['terminal_count'] : 0,
                "hand_count"    => $month ? (int)$month['hand_count'] : 0,
                "auth_count"    => $month ? (int)$month['auth_count'] : 0,
                "simple_count"  => $month ? (int)$month['simple_count'] : 0
            ],
            'appr'  => [
                'amount'=> $month ? (int)$month['appr_amount'] : 0,
                'count' => $month ? (int)$month['appr_count'] : 0,
            ],
            'cxl'   => [
                'amount'=> $month ? (int)$month['cxl_amount'] : 0,
                'count' => $month ? (int)$month['cxl_count'] : 0,
            ],
            'amount'    => $month ? $month['appr_amount'] + $month['cxl_amount'] : 0,
            'count'     => $month ? $month['appr_count'] + $month['cxl_count'] : 0,
            'profit'    => $month ? (int)$month['profit'] : 0,
        ];
    }
}
