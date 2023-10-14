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
use App\Http\Traits\Settle\SettleTerminalTrait;
use App\Http\Traits\Settle\TransactionTrait;

use App\Http\Requests\Manager\IndexRequest;

class SalesforceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait, TransactionTrait;
    protected $salesforces, $settleDeducts;

    public function __construct(Salesforce $salesforces, SettleDeductSalesforce $settleDeducts)
    {
        $this->salesforces = $salesforces;
        $this->settleDeducts = $settleDeducts;
    }

    private function commonSalesQuery($query, $s_dt, $e_dt)
    {
        if(Carbon::parse($e_dt)->isLastOfMonth())
        { // 조회 일이 말일이면 settle_cycle 28인 영업자도 조회
            $query = $query->where(function ($query) use ($e_dt) {
                $query->where('settle_day', Carbon::parse($e_dt)->dayOfWeek)
                    ->orWhereNull('settle_day');
            });
        }
        else
        {   // 말일이 아니라면 settle_cycle 28인 영업자는 제외
            $query = $query
                ->where('settle_day', Carbon::parse($e_dt)->dayOfWeek)
                ->where('settle_cycle', '!=', 28);
        }
        return $query->whereRaw("'$e_dt' > DATE_ADD(COALESCE(last_settle_dt, '2000-01-01'), INTERVAL(settle_cycle) DAY)");
    }

    private function commonQuery($request)
    {
        $validated = $request->validate(['s_dt'=>'required|date', 'e_dt'=>'required|date']);
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $cols   = array_merge($this->getDefaultCols(), ['sales_name', 'level', 'settle_cycle', 'settle_day', 'settle_tax_type', 'last_settle_dt']);
        $search = $request->input('search', '');
        $level  = $request->level;
        $s_dt   = $request->s_dt;
        $e_dt   = $request->e_dt;

        [$target_id, $target_settle_id] =  $this->getTargetInfo($level);
        $sales_ids = $this->getExistTransUserIds($target_id, $target_settle_id);
        $query = $this->getDefaultQuery($this->salesforces, $request, $sales_ids)
            ->where('sales_name', 'like', "%$search%")
            ->where('level', $level);

        if($request->settle_cycle)
            $query = $query->where('settle_cycle', $request->settle_cycle);

        $query = $this->commonSalesQuery($query, $s_dt, $e_dt);
        $query = $query->with(['transactions', 'deducts']);
        $data = $this->getIndexData($request, $query, 'id', $cols, "created_at", false);
        $data = $this->getSettleInformation($data, $settle_key);
        // set terminals
        if(count($sales_ids))
        {
            $settle_s_day   = date('d', strtotime($request->s_dt));
            $settle_e_day   = date('d', strtotime($request->e_dt));
            $pay_modules = collect(
                Merchandise::join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
                    ->whereIn("merchandises.".$target_id, $sales_ids)
                    ->where('payment_modules.comm_settle_day', '>=', $settle_s_day)
                    ->where('payment_modules.comm_settle_day', '<=', $settle_e_day)
                    ->where('payment_modules.comm_calc_level', $level)
                    ->where('payment_modules.begin_dt', '<', $request->s_dt)
                    ->get(["payment_modules.*", "merchandises.".$target_id])
            );
            $data = $this->setTerminalCost($data, $pay_modules, $request->s_dt, $request->s_dt, $target_id);
        }
        // set total settle
        $data = $this->setSettleFinalAmount($data);
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
                'under_sales_amount' => 0,
            ],
            'settle' => [
                'amount' => 0,
                'deposit' => 0,
                'transfer' => 0,
            ]
        ];
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $transactions = collect();
        $data = $this->commonQuery($request);
        foreach($data['content'] as $data)
        {
            $transactions = $transactions->merge($data->transactions);
            $total['deduction']['amount'] += $data->deduction['amount'];
            $total['terminal']['amount'] += $data->terminal['amount'];
            $total['terminal']['under_sales_amount'] += $data->terminal['under_sales_amount'];
            $total['settle']['amount'] += $data->settle['amount'];
            $total['settle']['deposit'] += $data->settle['deposit'];
            $total['settle']['transfer'] += $data->settle['transfer'];
        }
        $chart = getDefaultTransChartFormat($transactions, $settle_key);
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
        $level  = $request->level;
        $s_dt   = $request->s_dt;
        $e_dt   = $request->e_dt;

        $query = $this->salesforces->where('id', $request->id);
        $sales = $this->commonSalesQuery($query, $s_dt, $e_dt)->first();
        if($sales)
        {
            [$target_id, $target_settle_id] = $this->getTargetInfo($level);
            $data = $this->partSettleCommonQuery($request, $target_id, $target_settle_id);
        }
        else
        {
            $data = [
                'content' => [],
                'page' => $request->page,
                'page_size' => $request->page_size,
                'total' => 0,
            ];
        }
        return $this->response(0, $data);
    }

    public function partChart(Request $request)
    {
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
        ]);
        
        $level  = $request->level;
        $s_dt   = $request->s_dt;
        $e_dt   = $request->e_dt;
        
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $query = $this->salesforces->where('id', $request->id);
        $sales = $this->commonSalesQuery($query, $s_dt, $e_dt)->first();
        if($sales)
        {
            $idx = globalLevelByIndex($request->level);
            $key = 'sales'.$idx;
    
            $query = Transaction::where($key.'_id', $request->id)
                ->globalFilter()
                ->settleFilter($key."_settle_id")
                ->settleTransaction();
    
            $query = globalPGFilter($query, $request);
            $query = globalSalesFilter($query, $request);
            $query = globalAuthFilter($query, $request);
    
            $data = $this->getIndexData($request, $query);
            $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
            $salesforces    = globalGetSalesByIds($sales_ids);
            $data['content'] = globalMappingSales($salesforces, $data['content']);
            $chart = getDefaultTransChartFormat($data['content'], $settle_key);
        }
        else
        {
            $chart = getDefaultTransChartFormat([], $settle_key);
        }
        return $this->response(0, $chart);     
    }

    public function partSettle(Request $request)
    {
        echo $request->selected;
    }
}
