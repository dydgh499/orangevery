<?php

namespace App\Http\Controllers\Manager;

use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Transaction;
use App\Models\Log\SettleDeductMerchandise;
use App\Models\Log\SettleDeductSalesforce;

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
        foreach($data['content'] as $content) {
            $chart = getDefaultTransChartFormat($content->transactions);
            $content->amount = $chart['amount'];
            $content->count = $chart['count'];
            $content->profit = $chart['profit'];
            $content->trx_amount = $chart['trx_amount'];
            $content->hold_amount = $chart['hold_amount'];
            $content->settle_fee = $chart['settle_fee'];
            $content->total_trx_amount = $chart['total_trx_amount'];
            $content->appr = $chart['appr'];
            $content->cxl = $chart['cxl'];
            $content->deduction = [
                'input' => null,
                'amount' => $content->deducts->sum('amount'),
            ];
            $content->terminal = [
                'amount' => 0,   
            ];
            $content->settle = [
                'amount'    => $chart['profit'] + $content->deduction['amount'],
                'deposit'   => $chart['profit'] + $content->deduction['amount'],
                'transfer'  => $chart['profit'] + $content->deduction['amount'],
            ];
            $content->makeHidden(['transactions']);
        }
        return $data;
    }

    private function getDefaultQuery($query, $request, $ids)
    {
        return $query
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->whereIn('id', $ids);
    }

    private function getExistTransUserIds($date, $col)
    {
        return Transaction::settleFilter()
            ->settleTransaction($date)
            ->distinct()
            ->get([$col])
            ->pluck([$col]);
    }

    public function merchandiseChart(Request $request)
    {

    }

    public function salesforceChart(Request $request)
    {

    }

    public function merchandiseCommonQuery($request)
    {

    }

    public function merchandises(IndexRequest $request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        $cols = array_merge($this->cols, ['mcht_name']);
        $search = $request->input('search', '');
        $date   = $request->dt;

        $mcht_ids = $this->getExistTransUserIds($date, 'mcht_id');
        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
                ->where('mcht_name', 'like', "%$search%");
            
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);
        $query = $query->with(['transactions', 'deducts']);

        $data = $this->getIndexData($request, $query, 'id', $cols);
        $data = $this->getSettleInformation($data); 
        return $this->response(0, $data);
    }

    public function salesforceCommonQuery($request)
    {

    }
    public function salesforces(IndexRequest $request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        $cols   = array_merge($this->cols, ['sales_name', 'level', 'settle_cycle', 'settle_day', 'settle_tax_type', 'last_settle_dt']);
        $search = $request->input('search', '');
        $level  = $request->level;
        $date   = $request->dt;

        $idx = globalLevelByIndex($level);
        $sales_ids = $this->getExistTransUserIds($date, 'sales'.$idx.'_id');
        $query = $this->getDefaultQuery($this->salesforces, $request, $sales_ids)
            ->where('sales_name', 'like', "%$search%")
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
        $query = $query->with(['transactions', 'deducts']);
        $data = $this->getIndexData($request, $query, 'id', $cols);

        $salesforces = globalGetIndexingByCollection($data['content']);
        foreach($data['content'] as $content)
        {
            $content->transactions = globalMappingSales($salesforces, $content->transactions);
        }

        $data = $this->getSettleInformation($data); 
        return $this->response(0, $data);
    }

    private function deduct($orm, $col, $request)
    {
        $validated = $request->validate(['amount'=>'required|integer', 'dt'=>'required|date', 'id'=>'required']);
        $res = $orm->create([
            'brand_id'  => $request->user()->brand_id,
            'amount'    => $request->amount * -1,
            'deduct_dt' => $request->dt,
            $col   => $request->id,
        ]);
        return $this->response(1);
    }

    function MchtDeduct(Request $request) 
    {
        return $this->deduct(new SettleDeductMerchandise(), 'mcht_id', $request);
    }

    function SalesDeduct(Request $request) 
    {
        return $this->deduct(new SettleDeductSalesforce(), 'sales_id', $request);
    }
}
