<?php

namespace App\Http\Controllers\QuickView;

use App\Models\Transaction;

use App\Http\Controllers\Manager\TransactionController;
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
        $transactions = [];
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
        foreach($contents as $content) 
        {
            $trxDate = Carbon::parse($content->trx_dt);            
            if ($trxDate->isToday()) 
                $grouped[$str_now][] = $content;
            if ($trxDate->year === $now->year && $trxDate->month === $now->month) 
                $grouped[$str_cur_month][] = $content;
            if ($trxDate->year === $last_month->year && $trxDate->month === $last_month->month) 
                $grouped[$str_last_month][] = $content;
        }
        foreach($grouped as $key => $group)
        {
            $transactions[$key] = getDefaultTransChartFormat(collect($group));
        }
        return $transactions;
    }

    private function groupedBy30Days($contents)
    {
        $grouped = [];
        $transactions = [];
        $now = Carbon::now();
        $ago_30_days = $now->copy()->subDays(30);
        foreach($contents as $content) 
        {
            $trxDate = Carbon::parse($content->trx_dt);
    
            if ($trxDate->format('Y-m-d') >= $ago_30_days->format('Y-m-d') && $trxDate->format('Y-m-d') <= $now->format('Y-m-d')) {
                $groupKey = $trxDate->format('Y-m-d');
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [];
                }
                $grouped[$groupKey][] = $content;
            }
        }
        foreach($grouped as $key => $group)
        {
            $transactions[$key] = getDefaultTransChartFormat(collect($group));

        }
        return $transactions;
    }

    private function groupedByMchtName($contents)
    {
        $grouped = [];
        $transactions = [];
        $now = Carbon::now();
        $ago_30_days = $now->copy()->subDays(30);
        
        if($contents)
        {
            $content_in_30_days = $contents->filter(function ($content) use ($ago_30_days, $now) {
                $trxDate = Carbon::parse($content->trx_dt);
                return $trxDate->format('Y-m-d') >= $ago_30_days->format('Y-m-d') && $trxDate->format('Y-m-d') <= $now->format('Y-m-d');
            })->all();    
        }
        else
            $content_in_30_days = [];
            
        foreach ($content_in_30_days as $content) 
        {
            if (!isset($grouped[$content->mcht_name]))
                $grouped[$content->mcht_name] = [];
            $grouped[$content->mcht_name][] = $content;
        }
        foreach($grouped as $key => $group)
        {
            $transactions[$key] = getDefaultTransChartFormat(collect($group));
        }
        return $transactions;
    }

    public function index(Request $request)
    {
        $one_month_ago = Carbon::now()->subMonths(1)->startOfMonth()->format('Y-m-d');
        $request->merge([
            'page' => '1',
            'page_size'=> '9999999',
            's_dt' => $one_month_ago,
            'e_dt' => '2999-01-01',
        ]);
        $controller = new TransactionController(new Transaction);

        $query = $controller->commonSelect($request);
        $data   = $controller->getIndexData($request, $query, 'transactions.id', $controller->cols, 'transactions.id');

        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);

        foreach($data['content'] as $content) 
        {
            $content->append(['total_trx_amount']);
        }
        $grouped = [
            'day' =>  $this->groupedBy30Days($data['content']),
            'month' => $this->groupedByMonth($data['content']),
            'mcht_name' => $this->groupedByMchtName($data['content']),
        ];
        return $this->response(0, $grouped);
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
