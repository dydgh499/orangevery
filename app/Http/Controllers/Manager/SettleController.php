<?php

namespace App\Http\Controllers\Manager;

use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Logs\SettleDeductMerchandise;
use App\Models\Logs\SettleDeductSalesforce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;


class SettleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $merchandises, $salesforces, $cols;

    public function __construct(Merchandise $merchandises, Salesforce $salesforces)
    {
        $this->merchandises = $merchandises;
        $this->salesforces = $salesforces;
        $this->cols = [
            'id', 'user_name', 'addr',
            'phone_num', 'sector', 'business_num', 'resident_num',
            "acct_num", "acct_name", "acct_bank_name", "acct_bank_code",
        ];
    }

    public function getSettleInformation($data)
    {
        $division = function($item) {
            return $item->is_cancel == true;
        };        
        $totals = function($items) {
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
        foreach($data['content'] as $content) {
            $appr = $content->transactions->filter(function ($transaction) use ($division) {
                return $division($transaction) == false;
            })->values();
        
            $cxl = $content->transactions->filter(function ($transaction) use ($division) {
                return $division($transaction) == true;
            })->values();

            $content->appr  = $totals($appr);
            $content->cxl   = $totals($cxl);

            $content->amount    = $content->appr['amount'] + $content->cxl['amount'];
            $content->count     = $content->appr['count'] + $content->cxl['count'];
            $content->trx_amount = $content->appr['trx_amount'] + $content->cxl['trx_amount'];
            $content->total_trx_amount = $content->appr['total_trx_amount'] + $content->cxl['total_trx_amount'];
            $content->settle_fee = $content->appr['settle_fee'] + $content->cxl['settle_fee'];
            $content->hold_amount = $content->appr['hold_amount'] + $content->cxl['hold_amount'];
            $content->profit = $content->appr['profit'] + $content->cxl['profit'];

            $content->deduction = [
                'input' => null,
                'amount' => $content->deducts->sum('amount'),
            ];
            $content->terminal = [
                'amount' => 0,   
            ];
            $content->settle = [
                'amount'    => $content->profit + $content->deduction['amount'],
                'deposit'   => $content->profit + $content->deduction['amount'],
                'transfer'  => $content->profit + $content->deduction['amount'],
            ];
            $content->makeHidden(['transactions']);
        }
        return $data;
    }

    public function merchandises(IndexRequest $request)
    {
        $validated = $request->validate(['dt'=>'required|date']);

        $cols = array_merge($this->cols, ['mcht_name']);
        $search = $request->input('search', '');
        $date   = $request->dt;

        $query = $this->merchandises
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where('mcht_name', 'like', "%$search%");
            
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);
        $query = $query->with(['transactions' => function ($query) use ($date) {
            $query->where(function ($query) use ($date) {   // 승인이면서 D+1 적용
                $query->whereRaw("trx_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)")
                    ->where('is_cancel', false);
            })->orWhere(function ($query) use ($date) {     // 취소이면서 D+1 적용
                $query->whereRaw("cxl_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)")
                    ->where('is_cancel', true);
            });
        }, 'deducts']);

        $data = $this->getIndexData($request, $query, 'id', $cols);
        $data = $this->getSettleInformation($data); 
        return $this->response(0, $data);
    }

    public function salesforces(IndexRequest $request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        $cols   = array_merge($this->cols, ['nick_name','level', 'settle_cycle', 'settle_day', 'settle_tax_type', 'last_settle_dt']);
        $search = $request->input('search', '');
        $level  = $request->level;
        $date   = $request->dt;

        $query = $this->salesforces
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where('user_name', 'like', "%$search%")
            ->where('level', $level);

        if(isSalesforce($request))
            $query = $query->where('id', $request->user()->id);
        if($request->settle_cycle)
            $query = $query->where('settle_cycle', $request->settle_cycle);
        if(Carbon::parse($date)->isLastOfMonth()) 
        { // 조회 일이 말일이면 settle_cycle 28인 영업자도 조회
            $query = $query->where(function ($query) use ($date) {
                $query->where('settle_day', Carbon::parse($date)->dayOfWeek)
                    ->orWhereNull('settle_day');
            });            
        }
        else
        { // 말일이 아니라면 settle_cycle 28인 영업자는 제외
            $query = $query
                ->where('settle_day', Carbon::parse($date)->dayOfWeek)
                ->where('settle_cycle', '!=', 28);
        }
        $query = $query->whereRaw("'$date' > DATE_ADD(COALESCE(last_settle_dt, '2000-01-01'), INTERVAL(settle_cycle) DAY)");
        $query = $query->with(['transactions' => function ($query) use ($date) {
            $query->where(function ($query) use ($date) {   // 승인이면서 D+1 적용
                $query->whereRaw("trx_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)")
                    ->where('is_cancel', false);
            })->orWhere(function ($query) use ($date) {     // 취소이면서 D+1 적용
                $query->whereRaw("cxl_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)")
                    ->where('is_cancel', true);
            });
        }, 'deducts']);

        $data = $this->getIndexData($request, $query, 'id', $cols);

        $salesforces = globalGetIndexingByCollection($data['content']);
        foreach($data['content'] as $content)
        {
            $content->transactions = globalMappingSales($salesforces, $content->transactions);
        }


        $data = $this->getSettleInformation($data); 
        return $this->response(0, $data);
    }

    function MchtDeduct(Request $request) 
    {
        $validated = $request->validate(['amount'=>'required|integer', 'dt'=>'required|date', 'id'=>'required']);
        $res = SettleDeductMerchandise::create([
            'brand_id'  => $request->user()->brand_id,
            'mcht_id'   => $request->id,
            'amount'    => $request->amount * -1,
            'deduct_dt' => $request->dt,
        ]);
        return $this->response(1);
    }

    function SalesDeduct(Request $request) 
    {
        $validated = $request->validate(['amount'=>'required|integer', 'dt'=>'required|date', 'id'=>'required']);
        $res = SettleDeductSalesforce::create([
            'brand_id'  => $request->user()->brand_id,
            'sales_id'   => $request->id,
            'amount'    => $request->amount * -1,
            'deduct_dt' => $request->dt,
        ]);
        return $this->response(1);
    }
}
