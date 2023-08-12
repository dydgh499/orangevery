<?php

namespace App\Http\Controllers\Log;

use Illuminate\Support\Facades\DB;
use App\Models\Log\SettleHistoryMerchandise;
use App\Models\Log\SettleHistorySalesforce;
use App\Models\Transaction;
use App\Models\Salesforce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleHistoryTrait;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;


class SettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait;
    protected $settle_mcht_hist, $settle_sales_hist, $add_cols;
    
    public function __construct(SettleHistoryMerchandise $settle_mcht_hist, SettleHistorySalesforce $settle_sales_hist)
    {
        $this->settle_mcht_hist = $settle_mcht_hist;
        $this->settle_sales_hist = $settle_sales_hist;
    }

    public function indexMerchandise(IndexRequest $request)
    {
        $cols = ['merchandises.user_name', 'merchandises.mcht_name', 'settle_histories_merchandises.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_mcht_hist
                ->join('merchandises', 'settle_histories_merchandises.mcht_id', 'merchandises.id')
                ->where('settle_histories_merchandises.brand_id', $request->user()->brand_id)
                ->where('settle_histories_merchandises.is_delete', false)
                ->where('merchandises.mcht_name', 'like', "%$search%");

        $query = globalSalesFilter($query, $request, 'merchandises');
        $query = globalAuthFilter($query, $request, 'merchandises');

        $data = $this->getIndexData($request, $query, 'settle_histories_merchandises.id', $cols, 'settle_histories_merchandises.created_at');
        return $this->response(0, $data);
    }

    public function indexSalesforce(IndexRequest $request)
    {
        $cols = ['salesforces.user_name', 'salesforces.level', 'settle_histories_salesforces.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_sales_hist
                ->join('salesforces', 'settle_histories_salesforces.sales_id', 'salesforces.id')
                ->where('settle_histories_salesforces.brand_id', $request->user()->brand_id)
                ->where('settle_histories_salesforces.level', $request->level)
                ->where('settle_histories_salesforces.is_delete', false)
                ->where('salesforces.user_name', 'like', "%$search%");

        if(isSalesforce($request) && $request->level == $request->user()->level)
            $query = $query->where('salesforces.id', $request->user()->id);

        $data = $this->getIndexData($request, $query, 'settle_histories_salesforces.id', $cols, 'settle_histories_salesforces.created_at');
        return $this->response(0, $data);
    }

    public function createMerchandise(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $query = Transaction::where('mcht_id', $request->id);
            $res = $this->createMerchandiseCommon($request, $query);
            return $this->response($res ? 1 : 990);    
        });
    }

    public function createMerchandisePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $query = Transaction::whereIn('id', $request->selected);
            $res = $this->createMerchandiseCommon($request, $query);
            return $this->response($res ? 1 : 990);    
        });
    }

    public function createSalesforce(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::where($target_id, $request->id);
            return $this->createSalesforceCommon($request, $query, $target_id, $target_settle_id);
        });
    }

    public function createSalesforcePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::whereIn('id', $request->selected);
            return $this->createSalesforceCommon($request, $query, $target_id, $target_settle_id);
        });
    }


    public function deleteMerchandise(Request $request, $id)
    {
        return DB::transaction(function () use($request, $id) {
            $res = $this->deleteMchtforceCommon( $request, $id, 'mcht_id', 'mcht_settle_id', 'mcht_id');
            return $this->response($res ? 1 : 1000);
        });
    }

    public function deleteSalesforce(Request $request, $id)
    {
        return DB::transaction(function () use($request, $id) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $res = $this->deleteSalesforceCommon($request, $id, $target_id, $target_settle_id, 'sales_id');
            return $this->response($res ? 1 : 1000);
        });
    }
    
    public function depositMerchandise(Request $request, $id)
    {
        return $this->deposit($this->settle_mcht_hist, $id);
    }

    public function depositSalesforce(Request $request, $id)
    {
        return $this->deposit($this->settle_sales_hist, $id);
    }
}
