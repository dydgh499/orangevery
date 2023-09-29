<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\SettleDeductMerchandise;
use App\Models\PaymentModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Traits\Settle\SettleTerminalTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait, TransactionTrait;
    protected $merchandises, $settleDeducts;

    public function __construct(Merchandise $merchandises, SettleDeductMerchandise $settleDeducts)
    {
        $this->merchandises = $merchandises;
        $this->settleDeducts = $settleDeducts;

    }
    private function commonQuery($request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        [$settle_key, $group_key] = $this->getSettleCol($request);

        $cols = array_merge($this->getDefaultCols(), ['mcht_name']);
        $search = $request->input('search', '');

        $mcht_ids = $this->getExistTransUserIds('mcht_id', 'mcht_settle_id');
        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
                ->where('mcht_name', 'like', "%$search%");            
        $query = $query->with(['transactions', 'deducts']);
        $data = $this->getIndexData($request, $query, 'id', $cols);
        $data = $this->getSettleInformation($data, $settle_key); 
        // set terminals
        $mcht_ids = collect($data['content'])->pluck('id')->all();
        if(count($mcht_ids))
        {
            $settle_day     = date('d', strtotime($request->dt));
            $pay_modules    = collect(
                PaymentModule::whereIn('mcht_id', $mcht_ids)
                ->where('comm_settle_day', $settle_day)
                ->where('comm_calc_level', 10)
                ->where('begin_dt', '<', $request->dt)
                ->get()
            );
            $data = $this->setTerminalCost($data, $pay_modules, $request->dt, 'mcht_id');
        }
        // set total settle
        $data = $this->setSettleFinalAmount($data);
        return $data;
    }

    public function chart(Request $request)
    {
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
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
        $data = $this->commonQuery($request);
        return $this->response(0, $data);
    }

    public function deduct(Request $request) 
    {
        return $this->commonDeduct($this->settleDeducts, 'mcht_id', $request);
    }

    public function part(Request $request)
    {
        $data = $this->partSettleCommonQuery($request, 'mcht_id', 'mcht_settle_id');
        return $this->response(0, $data);
    }

    public function partChart(Request $request)
    {
        $request = $request->merge([
            'page' => 1,
            'page_size' => 999999,
        ]);
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $query = Transaction::where('mcht_id', $request->id)
            ->globalFilter()
            ->settleFilter('mcht_settle_id')
            ->settleTransaction();
        $query = globalPGFilter($query, $request);
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);
        $cols = $this->getTotalCols($settle_key);
        $chart = $query->first($cols);
        $chart = $this->setTransChartFormat($chart);
        return $this->response(0, $chart);
    }
}
