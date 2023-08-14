<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Transaction;
use App\Models\Log\SettleDeductSalesforce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Requests\Manager\IndexRequest;

class SalesforceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait;
    protected $salesforces, $settleDeducts;

    public function __construct(Salesforce $salesforces, SettleDeductSalesforce $settleDeducts)
    {
        $this->salesforces = $salesforces;
        $this->settleDeducts = $settleDeducts;
    }

    private function commonQuery($request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        $cols   = array_merge($this->getDefaultCols(), ['sales_name', 'level', 'settle_cycle', 'settle_day', 'settle_tax_type', 'last_settle_dt']);
        $search = $request->input('search', '');
        $level  = $request->level;
        $date   = $request->dt;
        
        $idx = globalLevelByIndex($level);
        $key = 'sales'.$idx;
        
        $sales_ids = $this->getExistTransUserIds($date, $key.'_id', $key.'_settle_id');
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
        return $data;
    }

    public function chart(Request $request)
    {
        $request = $request->merge([
            'paze_size' => 999999,
        ]);
        $total = [
            'id' => '합계',
            'deduction' => [
                'input' => null,
                'amount' => 0,
            ],
            'terminal' => [
                'amount' => 0,
            ],
            'settle' => [
                'amount' => 0,
                'deposit' => 0,
                'transfer' => 0,
            ]
        ];
        $transactions = collect();
        $data = $this->commonQuery($request);
        foreach($data['content'] as $data)
        {
            $transactions = $transactions->merge($data->transactions);
            $total['deduction']['amount'] += $data->deduction['amount'];
            $total['terminal']['amount'] += $data->terminal['amount'];
            $total['settle']['amount'] += $data->settle['amount'];
            $total['settle']['deposit'] += $data->settle['deposit'];
            $total['settle']['transfer'] += $data->settle['transfer'];
        }
        $chart = getDefaultTransChartFormat($transactions);
        $total = array_merge($total, $chart);
        return $this->response(0, $total);
    }

    public function index(IndexRequest $request)
    {
        return $this->response(0, $this->commonQuery($request));
    }

    public function deduct(Request $request) 
    {
        return $this->commonDeduct($this->settleDeducts, 'sales_id', $request);
    }

    public function part(Request $request)
    {
        $idx = globalLevelByIndex($request->level);
        $key = 'sales'.$idx;

        $query = Transaction::where($key.'_id', $request->id)
            ->globalFilter()
            ->settleFilter($key."_settle_id")
            ->settleTransaction(request()->dt)
            ->with(['mcht']);

        $query = globalPGFilter($query, $request);
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);

        $data = $this->getIndexData($request, $query);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);

        foreach($data['content'] as $content) 
        {
            $content->mcht_name = $content->mcht['mcht_name'];
            $content->append(['total_trx_amount']);
            $content->makeHidden(['mcht']);
        }
        return $this->response(0, $data);
    }

    public function partChart(Request $request)
    {
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
        ]);
        $idx = globalLevelByIndex($request->level);
        $key = 'sales'.$idx;

        $query = Transaction::where($key.'_id', $request->id)
            ->globalFilter()
            ->settleFilter($key."_settle_id")
            ->settleTransaction($request->dt);

        $query = globalPGFilter($query, $request);
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);

        $data = $this->getIndexData($request, $query);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        foreach($data['content'] as $content) 
        {
            $content->append(['total_trx_amount']);
        }
        $chart = getDefaultTransChartFormat($data['content']);
        return $this->response(0, $chart);     
    }

    public function partSettle(Request $request)
    {
        echo $request->selected;
    }
}
