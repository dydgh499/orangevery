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
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\Log\CreateSettleHistoryRequest;


class SettleHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $settle_mcht_hist, $settle_sales_hist, $add_cols;
    
    public function __construct(SettleHistoryMerchandise $settle_mcht_hist, SettleHistorySalesforce $settle_sales_hist)
    {
        $this->settle_mcht_hist = $settle_mcht_hist;
        $this->settle_sales_hist = $settle_sales_hist;
    }
    
    private function SetTransSettle($request, $target_id, $target_settle_id, $resource_id)
    {
        $query = Transaction::where('brand_id', $request->user()->brand_id)
            ->where($target_id, $request->id)
            ->whereNull($target_settle_id)
            ->whereRaw("trx_dt < DATE_SUB('".$request->dt."', INTERVAL(mcht_settle_type) DAY)");
        $query = globalPGFilter($query, $request);
        return $query->update([$target_settle_id => $resource_id]);
    }

    
    private function SetNullTransSettle($request, $target_id, $target_settle_id, $user_id)
    {
        return Transaction::where('brand_id', $request->user()->brand_id)
            ->where($target_id, $user_id)
            ->where($target_settle_id, $request->id)
            ->update([$target_settle_id => null]);
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

        $data = $this->getIndexData($request, $query, 'settle_histories_merchandises.id', $cols, 'settle_histories_merchandises.settle_dt');
        return $this->response(0, $data);
    }

    public function indexSalesforce(IndexRequest $request)
    {
        $cols = ['salesforces.user_name', 'salesforces.level', 'settle_histories_salesforces.*'];
        $search = $request->input('search', '');
        $query  = $this->settle_sales_hist
                ->join('salesforces', 'settle_histories_salesforces.sales_id', 'salesforces.id')
                ->where('settle_histories_salesforces.brand_id', $request->user()->brand_id)
                ->where('settle_histories_salesforces.is_delete', false)
                ->where('salesforces.user_name', 'like', "%$search%");

        if(isSalesforce($request) && $request->level == $request->user()->level)
            $query = $query->where('salesforces.id', $request->user()->id);

        $data = $this->getIndexData($request, $query, 'settle_histories_salesforces.id', $cols, 'settle_histories_salesforces.settle_dt');
        return $this->response(0, $data);
    }

    public function createMerchandise(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {            
            $data = $request->data('mcht_id');
            $data['settle_fee'] = $request->settle_fee;

            $c_res = $this->settle_mcht_hist->create($data);
            $u_res = $this->SetTransSettle($request, 'mcht_id', 'mcht_settle_id', $c_res->id);            
            return $this->response($c_res && $u_res ? 1 : 990);    
        });
    }

    public function createSalesforce(CreateSettleHistoryRequest $request)
    {
        return DB::transaction(function () use($request) {
            $idx = globalLevelByIndex($request->level);
            $target_id =  'sales'.$idx.'_id';
            $target_settle_id = 'sales'.$idx.'_settle_id';

            $data = $request->data('sales_id');
            $data['level'] = $request->level;

            $c_res = $this->settle_sales_hist->create($data);
            $u_res = $this->SetTransSettle($request, $target_id, $target_settle_id, $c_res->id);
            $s_res = Salesforce::find($request->id)->update(['last_settle_dt' => $request->dt]);
            return $this->response($c_res && $u_res && $s_res ? 1 : 990);
        });
    }

    public function deleteMerchandise(Request $request, $id)
    {
        return DB::transaction(function () use($request) {
            $query = $this->settle_mcht_hist->find($request->id);
            $hist  = $query->first();
            if($hist)
            {
                $u_res = $this->SetNullTransSettle($request, 'mcht_id', 'mcht_settle_id', $hist->mcht_id);
                $d_res = $query->update(['is_delete' => true]);
                return $this->response($u_res && $d_res ? 1 : 990);        
            }
            else
                return $this->response(1000);
        });
    }

    public function deleteSalesforce(Request $request, $id)
    {
        return DB::transaction(function () use($request) {
            $query = $this->settle_sales_hist->find($id);
            $hist  = $query->first();
            if($hist)
            {
                $idx = globalLevelByIndex($hist->level);
                $target_id =  'sales'.$idx.'_id';
                $target_settle_id =  'sales'.$idx.'_settle_id';

                $u_res = $this->SetNullTransSettle($request, $target_id, $target_settle_id, $hist->sales_id);
                $d_res = $query->update(['is_delete' => true]);
                return $this->response($u_res && $d_res ? 1 : 990);    
            }
            else
                return $this->response(1000);
        });
    }

    public function depositMerchandise(Request $request, $id)
    {
        $query = $this->settle_mcht_hist->find($id);
        $hist = $query->first();
        if($hist)
        {
            $deposit_dt     = $hist->deposit_status ? null : date('Y-m-d');
            $deposit_status = !$hist->deposit_status;
            $res = $query->update(['deposit_dt'=>$deposit_dt, 'deposit_status'=>$deposit_status]);
            return $this->response($res ? 1 : 990);    
        }
        else
            return $this->response(1000);
    }

    public function depositSalesforce(Request $request, $id)
    {
        $query = $this->settle_sales_hist->find($id);
        $hist = $query->first();
        if($hist)
        {
            $deposit_dt     = $hist->deposit_status ? null : date('Y-m-d');
            $deposit_status = !$hist->deposit_status;
            $res = $query->update(['deposit_dt'=>$deposit_dt, 'deposit_status'=>$deposit_status]);
            return $this->response($res ? 1 : 990);    
        }
        else
            return $this->response(1000);
    }
}
