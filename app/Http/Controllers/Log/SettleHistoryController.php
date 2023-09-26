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
use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;

class SettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleHistoryTrait, UnderSalesTrait;
    protected $settle_mcht_hist, $settle_sales_hist, $add_cols;
    
    public function __construct(SettleHistoryMerchandise $settle_mcht_hist, SettleHistorySalesforce $settle_sales_hist)
    {
        $this->settle_mcht_hist = $settle_mcht_hist;
        $this->settle_sales_hist = $settle_sales_hist;
        $this->base_noti_url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';
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

    public function batchMerchandise(BatchSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            for ($i=0; $i < count($request->datas); $i++) 
            { 
                $data = $request->data('mcht_id', $request->datas[$i]);
                $query = Transaction::where('mcht_id', $data['mcht_id']);
                $c_res = $this->settle_mcht_hist->create($data);
                $u_res = $this->SetTransSettle($query, 'mcht_settle_id', $c_res->id);    
            }
            return $this->response($c_res && $u_res ? 1 : 990);    
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

    public function batchSalesforce(BatchSettleHistoryRequest $request)
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
            }
            return $this->response($c_res && $u_res ? 1 : 990);
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
        return $this->depofsit($this->settle_mcht_hist, $id);
    }

    public function depositSalesforce(Request $request, $id)
    {
        return $this->deposit($this->settle_sales_hist, $id);
    }

    /**
     * 재이체
     */
    public function settleDeposit(Request $request)
    {
        $validated = $request->validate(['trx_id'=>'required', 'mid'=>'required', 'tid'=>'required']);
        $data = $request->all();
        $url = $this->base_noti_url.'/deposit';
        $res = post($url, $data);
        return $this->response(1, $res['body']);
    }

    /**
     * 모아서 출금(정산)
     */
    public function settleCollect(CreateSettleHistoryRequest $request)
    {
        //mid, tid
        $trans = Transaction::where('mcht_id', $request->id)
            ->globalFilter()
            ->settleFilter('mcht_settle_id')
            ->settleTransaction()
            ->get();
        print_r(json_decode(json_encode($trans), true));
        $data = $request->all();
        $data['trx_ids'] = $trx_ids;
        $info = [
            'mcht_id'
        ];
        /*
        $url = $this->base_noti_url.'/deposit-collect-settle';
        $res = post($url, $data);
        */
        return $this->response(1);        
    }
}
