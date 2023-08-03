<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\SettleDeductMerchandise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Requests\Manager\IndexRequest;

class MerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait;
    protected $merchandises, $settleDeducts;

    public function __construct(Merchandise $merchandises, SettleDeductMerchandise $settleDeducts)
    {
        $this->merchandises = $merchandises;
        $this->settleDeducts = $settleDeducts;

    }
    private function commonQuery($request)
    {
        $validated = $request->validate(['dt'=>'required|date']);
        $cols = array_merge($this->getDefaultCols(), ['mcht_name']);
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
        return $this->commonDeduct($this->settleDeducts, 'mcht_id', $request);
    }

    public function partSettle(Request $request)
    {
        $trans = Transaction::where('mcht_id', $request->mcht_id)
            ->settleFilter()
            ->settleTransaction(request()->dt)
            ->select();
        return $this->response(1);
    }

    public function part(Request $request)
    {

    }

}
