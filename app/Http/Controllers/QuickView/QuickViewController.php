<?php

namespace App\Http\Controllers\QuickView;

use App\Models\Transaction;

use App\Http\Controllers\Manager\TransactionController; //지워

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QuickViewController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, TransactionTrait;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
    }

    private function get3MonthlyTransactions($request)
    {
        $one_month_ago = Carbon::now()->subMonths(1)->startOfMonth()->format('Y-m-d');
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols = $this->getTotalCols($settle_key);
        array_push($cols, DB::raw("DATE_FORMAT(trx_dt, '%Y-%m-%d') as day"));

        return $this->transactions
            ->where($group_key, $request->user()->id)
            ->whereBetween('trx_dt', [$one_month_ago, DB::raw('CURDATE()')])
            ->groupby(DB::raw("DATE_FORMAT(trx_dt, '%Y-%m-%d')"))
            ->orderBy('day', 'desc')
            ->get($cols);
    }

    private function getMchtRankTransactions($request)
    {
        $one_month_ago = Carbon::now()->subMonths(1)->startOfMonth()->format('Y-m-d');
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols = $this->getTotalCols($settle_key, 'merchandises.id');
        array_push($cols, 'merchandises.mcht_name');

        return $this->transactions
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.'.$group_key, $request->user()->id)
            ->whereBetween('transactions.trx_dt', [$one_month_ago, DB::raw('CURDATE()')])
            ->groupby('merchandises.id')
            ->orderBy('appr_amount', 'desc')
            ->get($cols);
    }

    private function groupedByMonth($contents)
    {
        $transactions = [];
        $now = Carbon::now();
        $last_month = $now->copy()->subMonth();
        
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

    public function smslinkSend(Request $request)
    {
        $sms = [];
        $sms['user_id'] = "onmir1234";               // SMS 아이디
        $sms['key'] = "u73u7mt294xt2r9av2fevgg7yb154xtt"; //인증키
        $sms['msg'] = $request->buyer_name."님\n아래 url로 접속해 결제를 진행해주세요.\n".$request->url;
        $sms['receiver'] = $request->phone_num;     // 수신번호
        $sms['sender']  ="07043232060";             // 발신번호
        $sms['subject'] = "[안녕하세요. ".$request->user()->mcht_name." 입니다.]";

        $res = asPost("https://apis.aligo.in/send/", $sms);
        return $this->response(1);
    }
}
