<?php

namespace App\Http\Controllers\Log;

use Illuminate\Support\Facades\DB;
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
use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;

class SalesSettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait, UnderSalesTrait;
    protected $settle_sales_hist;
    
    public function __construct(SettleHistorySalesforce $settle_sales_hist)
    {
        $this->settle_sales_hist = $settle_sales_hist;
    }


    public function index(IndexRequest $request)
    {
        $cols = ['salesforces.user_name', 'salesforces.sales_name', 'salesforces.level', 'settle_histories_salesforces.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_sales_hist
                ->join('salesforces', 'settle_histories_salesforces.sales_id', 'salesforces.id')
                ->where('settle_histories_salesforces.brand_id', $request->user()->brand_id)
                ->where('settle_histories_salesforces.is_delete', false)
                ->where('salesforces.user_name', 'like', "%$search%");

        if(isSalesforce($request))
        {
            $sales_ids = $this->underSalesFilter($request);
            // 하위가 1000명이 넘으면 ..?
            $query = $query->whereIn('salesforces.id', $sales_ids);
        }
        if($request->has('level'))
            $query = $query->where('settle_histories_salesforces.level', $request->level);
        $data = $this->getIndexData($request, $query, 'settle_histories_salesforces.id', $cols, 'settle_histories_salesforces.created_at');
        return $this->response(0, $data);
    }

    public function store(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::where($target_id, $request->id);
            return $this->createSalesforceCommon($request, $query, $target_settle_id);
        });
    }

    public function storePart(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $query = Transaction::whereIn('id', $request->selected);
            return $this->createSalesforceCommon($request, $query, $target_settle_id);
        });
    }

    public function batch(BatchSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            for ($i=0; $i < count($request->datas); $i++) 
            { 
                [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
                $data = $request->data('sales_id', $request->datas[$i]);
                $data['level'] = $request->level;

                $query = Transaction::where($target_id, $data['sales_id']);
                $c_res = $this->settle_sales_hist->create($data);
                $u_res = $this->SetTransSettle($query, $target_settle_id, $c_res->id);
                $s_res = Salesforce::where('id', $request->id)->update(['last_settle_dt' => $data['settle_dt']]);
                $p_res = $this->SetPayModuleLastSettleMonth($data, $target_settle_id, $c_res->id);
            }
            return $this->response($c_res ? 1 : 990);
        });
    }


    public function destroy(Request $request, $id)
    {
        return DB::transaction(function () use($request, $id) {
            [$target_id, $target_settle_id] = $this->getTargetInfo($request->level);
            $res = $this->deleteSalesforceCommon($request, $id, $target_id, $target_settle_id, 'sales_id');
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        });
    }

    public function setDeposit(Request $request, $id)
    {
        return $this->deposit($this->settle_sales_hist, $id);
    }
}
