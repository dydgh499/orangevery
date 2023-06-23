<?php

namespace App\Http\Controllers\Log;

use App\Models\Logs\SettleHistoryMerchandise;
use App\Models\Logs\SettleHistorySaleforce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;


class SettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $settle_mcht_hist, $settle_sales_hist, $add_cols;
    
    public function __construct(SettleHistoryMerchandise $settle_mcht_hist, SettleHistorySaleforce $settle_sales_hist)
    {
        $this->settle_mcht_hist = $settle_mcht_hist;
        $this->settle_sales_hist = $settle_sales_hist;
        $this->add_cols = [
            'id'        => 'required|integer',
            'acct_nm'   => 'required|integer', 
            'acct_num'  => 'required|', 
            'acct_bank_nm' => 'required', 
            'acct_bank_cd' => 'required', 
            'total_amount' => 'required|integer',
            'cxl_amount' => 'required|integer', 
            'appr_amount' => 'required|integer', 
            'deduct_amount' => 'required|integer',
            'settle_amount' => 'required|integer',
            'settle_dt' => 'required|date',
            'deposit_dt' => 'required|date',
            'status' => 'required',
        ];
    }

    public function indexMerchandise(IndexRequest $request)
    {
        $cols = ['merchandises.mcht_name', 'settle_histories_merchandises.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_mcht_hist
                ->join('merchandises', 'settle_histories_merchandises.mcht_id', 'merchandises.id')
                ->where('merchandises.mcht_name', 'like', "%$search%");
        $data = $this->getIndexData($request, $query, 'id', $cols, 'settle_histories_merchandises.settle_dt');
        return $this->response(0, $data);
    }

    public function indexSalesforce(IndexRequest $request)
    {
        $cols = ['salesforces.mcht_name', 'settle_histories_salesforces.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_sales_hist
                ->join('salesforces', 'settle_histories_salesforces.sales_id', 'salesforces.id')
                ->where('salesforces.user_name', 'like', "%$search%");
        $data = $this->getIndexData($request, $query, 'id', $cols, 'settle_histories_salesforces.settle_dt');
        return $this->response(0, $data);
    }

    public function createMerchandise(Request $request)
    {
        $validated = $request->validate($this->add_cols);
        $res = $this->settle_sales_hist->create([
            'mcht_id' => $request->id,
            'acct_nm' => $request->acct_nm,
            'acct_num' => $request->acct_num,
            'acct_bank_nm' => $request->acct_bank_nm,
            'acct_bank_cd' => $request->acct_bank_cd,
            'total_amount' => $request->total_amount,
            'cxl_amount' => $request->cxl_amount,
            'appr_amount' => $request->appr_amount,
            'deduct_amount' => $request->deduct_amount,
            'settle_amount' => $request->settle_amount,
            'settle_dt' => $request->settle_dt,
            'deposit_dt' => $request->deposit_dt,
            'status' => $request->status,
        ]);
        return $this->response($res ? 1 : 990);
    }

    public function createSalesforce(Request $request)
    {
        $validated = $request->validate($this->add_cols);
        $res = $this->settle_sales_hist->create([
            'sales_id' => $request->sales_id,
            'acct_nm' => $request->acct_nm,
            'acct_num' => $request->acct_num,
            'acct_bank_nm' => $request->acct_bank_nm,
            'acct_bank_cd' => $request->acct_bank_cd,
            'total_amount' => $request->total_amount,
            'cxl_amount' => $request->cxl_amount,
            'appr_amount' => $request->appr_amount,
            'deduct_amount' => $request->deduct_amount,
            'settle_amount' => $request->settle_amount,
            'settle_dt' => $request->settle_dt,
            'deposit_dt' => $request->deposit_dt,
            'status' => $request->status,
        ]);
        return $this->response($res ? 1 : 990);
    }
}
