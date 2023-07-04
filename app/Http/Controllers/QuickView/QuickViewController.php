<?php

namespace App\Http\Controllers\QuickView;

use App\Models\Transaction;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QuickViewController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    private function groupedByMonth($contents)
    {
        $now = Carbon::now();
        $last_month = $now->copy()->subMonth();
        
        $str_now = $now->format('Y-m-d');
        $str_cur_month = $now->format('Y-m');
        $str_last_month = $last_month->format('Y-m');
        
        $grouped = [
            $str_now => [],
            $str_cur_month => [],
            $str_last_month => [],
        ];
        foreach ($contents as $content) {
            $trxDate = Carbon::parse($content->trx_dt);            
            if ($trxDate->isToday()) 
                $grouped[$str_now][] = $content;
            if ($trxDate->year === $now->year && $trxDate->month === $now->month) 
                $grouped[$str_cur_month][] = $content;
            if ($trxDate->year === $last_month->year && $trxDate->month === $last_month->month) 
                $grouped[$str_last_month][] = $content;
        }
        return $this->setTotalAmount($grouped);
    }

    private function groupedBy30Days($contents)
    {
        $grouped = [];
        $now = Carbon::now();
        $ago_30_days = $now->copy()->subDays(30);
        foreach ($contents as $content) {
            $trxDate = Carbon::parse($content->trx_dt);
    
            if ($trxDate->format('Y-m-d') >= $ago_30_days->format('Y-m-d') && $trxDate->format('Y-m-d') <= $now->format('Y-m-d')) {
                $groupKey = $trxDate->format('Y-m-d');
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [];
                }
                $grouped[$groupKey][] = $content;
            }
        }
        return $this->setTotalAmount($grouped);    
    }

    private function groupedByMchtName($contents)
    {
        $grouped = [];
        $now = Carbon::now();
        $ago_30_days = $now->copy()->subDays(30);
        
        $content_in_30_days = $contents->filter(function ($content) use ($ago_30_days, $now) {
            $trxDate = Carbon::parse($content->trx_dt);
            return $trxDate->format('Y-m-d') >= $ago_30_days->format('Y-m-d') && $trxDate->format('Y-m-d') <= $now->format('Y-m-d');
        })->all();
        
        foreach ($content_in_30_days as $content) {
            if (!isset($grouped[$content->mcht_name]))
                $grouped[$content->mcht_name] = [];
            $grouped[$content->mcht_name][] = $content;
        }
        return $this->setTotalAmount($grouped);
    }

    private function setTotalAmount($grouped)
    {
        $transactions = [];
        $division = function($item) {
            return $item->is_cancel == true;
        };        
        $infos = function($items) {
            return [
                'amount'        => $items->sum('amount'),
                'count'         => $items->count(),
                'trx_amount'    => $items->sum('trx_amount'),
                'hold_amount'   => $items->sum('hold_amount'),
                'settle_fee'  => $items->sum('mcht_settle_fee'),
                'total_trx_amount'=> $items->sum('total_trx_amount'),
                'profit'    => $items->sum('profit'),
            ];
        };
        $totals = function($trans, $key) {
            return $trans['appr'][$key] + $trans['cxl'][$key];
        };
        foreach($grouped as $key => $group)
        {
            $group = collect($group);
            $appr = $group->filter(function ($group) use ($division) {
                return $division($group) == false;
            })->values();
        
            $cxl = $group->filter(function ($group) use ($division) {
                return $division($group) == true;
            })->values();

            $transactions[$key]['appr']  = $infos($appr);
            $transactions[$key]['cxl']   = $infos($cxl);

            $transactions[$key]['amount'] = $totals($transactions[$key], 'amount');
            $transactions[$key]['count'] = $totals($transactions[$key], 'count');
            $transactions[$key]['trx_amount'] = $totals($transactions[$key], 'trx_amount');
            $transactions[$key]['total_trx_amount'] = $totals($transactions[$key], 'total_trx_amount');
            $transactions[$key]['settle_fee'] = $totals($transactions[$key], 'settle_fee');
            $transactions[$key]['hold_amount'] = $totals($transactions[$key], 'hold_amount');
            $transactions[$key]['profit'] = $totals($transactions[$key], 'profit');
        }
        return $transactions;
    }

    function index(Request $request)
    {
        $one_month_ago = Carbon::now()->subMonths(1)->startOfMonth();
        $request->merge([
            'page' => '1',
            'page_size'=> '9999999',
        ]);
        $query = Transaction::where('is_delete', false)
            ->where('brand_id', $request->user()->brand_id)
            ->where(function($query) use($one_month_ago) {
                $query->where('trx_dt', '>=', $one_month_ago)
                    ->orWhere('cxl_dt', '>=', $one_month_ago);
            });
        $query = globalAuthFilter($query, $request);        
        $data = $this->getIndexData($request, $query);

        $grouped = [
            'day' =>  $this->groupedBy30Days($data['content']),
            'month' => $this->groupedByMonth($data['content']),
            'mcht_name' => $this->groupedByMchtName($data['content']),
        ];
        return $this->response(0, $grouped);
    }
}
