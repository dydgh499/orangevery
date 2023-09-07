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
use App\Http\Traits\Salesforce\UnderSalesTrait;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;


class SettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait, UnderSalesTrait;
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
        $cols = ['salesforces.user_name', 'salesforces.sales_name', 'salesforces.level', 'settle_histories_salesforces.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_sales_hist
                ->join('salesforces', 'settle_histories_salesforces.sales_id', 'salesforces.id')
                ->where('settle_histories_salesforces.brand_id', $request->user()->brand_id)
                ->where('settle_histories_salesforces.level', $request->level)
                ->where('settle_histories_salesforces.is_delete', false)
                ->where('salesforces.user_name', 'like', "%$search%");

        if(isSalesforce($request))
        {
            if($request->user()->level == $request->level)
                $query = $query->where('salesforces.id', $request->user()->id);
            else
            {
                if($request->input('level', false))
                {
                    $rq_idx = globalLevelByIndex($request->level);
                    $s_keys = ['sales'.$rq_idx.'_id'];
                }
                else
                {
                    $levels  = $this->getUnderSalesLevels($request);
                    $s_keys = $this->getUnderSalesKeys($levels);
                }
                $sales = $this->getUnderSalesIds($request, $s_keys);
                $sales_ids = $sales->flatMap(function ($sale) use($s_keys) {
                    $keys = [];
                    foreach($s_keys as $s_key)
                    {
                        $keys[] = $sale[$s_key];
                    }
                    return $keys;
                })->unique();
                // 하위가 1000명이 넘으면 ..?
                if(count($sales_ids))
                    $query = $query->whereIn('salesforces.id', $sales_ids);
            }
        }

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
            return $this->createSalesforceCommon($request, $query, $target_settle_id);
        });
    }

    public function createSalesforcePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::whereIn('id', $request->selected);
            return $this->createSalesforceCommon($request, $query, $target_settle_id);
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
