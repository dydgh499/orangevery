<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Transaction;
use App\Models\PaymentModule;
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

/**
 * @group Settle-Salesforce API
 *
 * 영업점 정산 관리 API 입니다.
 */
class SalesforceController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait, TransactionTrait;
    protected $salesforces, $settleDeducts;

    public function __construct(Salesforce $salesforces, SettleDeductSalesforce $settleDeducts)
    {
        $this->salesforces = $salesforces;
        $this->settleDeducts = $settleDeducts;
    }

    protected function getTerminalSettleIds($request, $level, $target_id)
    {
        $query = PaymentModule::terminalSettle($level)
            ->join('salesforces', "merchandises.$target_id", '=', 'salesforces.id')
            ->where('salesforces.sales_name', 'like', "%".$request->search."%");
        $query = globalAuthFilter($query, $request, 'merchandises');
        $query = $this->commonSalesQuery($query, $request->s_dt, $request->e_dt);
        return $query->byTargetIds($target_id);
    }

    protected function commonSalesQuery($query, $s_dt, $e_dt)
    {
        // 말일이 아니라면 settle_cycle 28인 영업자는 제외
        if(Carbon::parse($e_dt)->isLastOfMonth() == false)
            $query = $query->where('salesforces.settle_cycle', '!=', 28);

        return $query->where(function ($query) use ($e_dt) {
            $query->where('settle_day', Carbon::parse($e_dt)->dayOfWeek)
                ->orWhereNull('settle_day');
        })->whereRaw("'$e_dt' > DATE_ADD(COALESCE(last_settle_dt, '2000-01-01'), INTERVAL(settle_cycle) DAY)");
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
        // ----- 영업점 목록 조회 ---------
        $sales_ids = $this->getExistTransUserIds($target_id, $target_settle_id);   
        #$terminal_settle_ids = $this->getTerminalSettleIds($request, $level, $target_id);

        $query = $this->getDefaultQuery($this->salesforces, $request, $sales_ids)
            ->where('sales_name', 'like', "%$search%")
            ->where('level', $level);
            /*
            ->orWhere(function ($query) use($terminal_settle_ids) {    
                $query->whereIn('id', $terminal_settle_ids);
            });
            */
        if($request->settle_cycle)
            $query = $query->where('settle_cycle', $request->settle_cycle);
           
        $query = $this->commonSalesQuery($query, $s_dt, $e_dt);
        $query = $query->with(['transactions', 'deducts']);
        $data = $this->getIndexData($request, $query, 'id', $cols, "created_at", false);

        $data = $this->getSettleInformation($data, $settle_key);
        // set terminals
        if(count($sales_ids)) #&& $terminal_settle_ids)
        {
            $settle_s_day   = date('d', strtotime($request->s_dt));
            $settle_e_day   = date('d', strtotime($request->e_dt));
            $settle_month   = date('Ym', strtotime($request->e_dt)); //202310
            $pay_modules = collect(
                PaymentModule::terminalSettle($level)
                    ->get(["payment_modules.*", "merchandises.".$target_id])
            );
            $data = $this->setTerminalCost($data, $pay_modules, $request->s_dt, $request->s_dt, $target_id);
        }
        // set total settle
        $data = $this->setSettleFinalAmount($data);
        return $data;
    }
    
    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(Request $request)
    {
        $request = $request->merge([
            'page' => 1,
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
            $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
            $salesforces    = globalGetSalesByIds($sales_ids);
            $data['content'] = globalMappingSales($salesforces, $data['content']);            
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
        $search = $request->input('search', '');
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
        ]);

        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols  = $this->getTotalCols($settle_key);
        $query = $this->salesforces->where('id', $request->id);
        $sales = $this->commonSalesQuery($query, $request->s_dt, $request->e_dt)->first();
        if($sales)
        {
            $idx = globalLevelByIndex($request->level);
            $chart = Transaction::where($group_key, $request->id)
                ->noSettlement("sales".$idx."_settle_id")
                ->where(function ($query) use ($search) {
                    return $query->where('transactions.mid', 'like', "%$search%")
                        ->orWhere('transactions.tid', 'like', "%$search%")
                        ->orWhere('transactions.trx_id', 'like', "%$search%")
                        ->orWhere('transactions.appr_num', 'like', "%$search%");
                })
                ->first($cols);
            $chart = $this->setTransChartFormat($chart);
        }
        else
            $chart = $this->setTransChartFormat([]);
        
        return $this->response(0, $chart);     
    }

    public function partSettle(Request $request)
    {
        echo $request->selected;
    }
}
