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
    protected $merchandises, $salesforces;

    public function __construct(Merchandise $merchandises, Salesforce $salesforces)
    {
        $this->merchandises = $merchandises;
        $this->salesforces = $salesforces;
        $this->cols = [
            'id', 'user_name', 'addr', 'detail_addr',
            'phone_num', 'sector', 'business_num', 'resident_num',
            "acct_num", "acct_nm", "acct_bank_nm", "acct_bank_cd",
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
                'trx_amount'      => $items->sum('trx_amount'),
                'pay_cond_amount'  => $items->sum('settle_fee'),
                'hold_amount'      => $items->sum('hold_amount'),
                'profit'        => $items->sum('profit'),
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
            $content->pay_cond_amount = $content->appr['pay_cond_amount'] + $content->cxl['pay_cond_amount'];
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
            ->where('mcht_name', 'like', "%$search%");
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);
        $query = $query->with(['transactions' => function ($query) use ($date) {
            $query->whereRaw("trx_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)");
        }, 'deducts']);
        

        $data = $this->getIndexData($request, $query, 'id', $cols);
        $data = $this->getSettleInformation($data); 
        return $this->response(0, $data);
    }

    public function salesforces(IndexRequest $request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        $cols   = array_merge($this->cols, ['nick_name']);
        $search = $request->input('search', '');
        $level  = $request->level;
        $date   = $request->dt;

        $query = $this->salesforces
            ->where('brand_id', $request->user()->brand_id)
            ->where('user_name', 'like', "%$search%")
            ->where('level', $level)
            ->where('settle_day', Carbon::parse($date)->dayOfWeek)
            ->whereRaw("'$date' > DATE_ADD(COALESCE(last_settle_dt, '2000-01-01'), INTERVAL(settle_cycle) DAY)");
        if($request->settle_cycle)
            $query = $query->where('settle_cycle', $request->settle_cycle);

        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);
        
        $query = $query->with(['transactions' => function ($query) use ($date) {
            $query->whereRaw("trx_dt < DATE_SUB('$date', INTERVAL(mcht_settle_type) DAY)");
        }, 'deducts']);
        $data = $this->getIndexData($request, $query, 'id', $cols);
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
