<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\DangerTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

class DangerTransController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $danger_transactions;

    public function __construct(DangerTransaction $danger_transactions)
    {
        $this->danger_transactions = $danger_transactions;
    }

    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'danger_transactions.*',
            'merchandises.mcht_name',
            'transactions.module_type',
            'transactions.item_name',
            'transactions.ord_num',
            'transactions.trx_id',
            'transactions.mid',
            'transactions.tid',
            'transactions.issuer',
            'transactions.acquirer',
            'transactions.card_num',
            'transactions.appr_num',
            'transactions.installment',
            'transactions.pg_id',
            'transactions.ps_id',
            'transactions.terminal_id',
            'transactions.amount',
            'transactions.buyer_name',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
        ];
        $search = $request->input('search', '');
        $query  = $this->danger_transactions
            ->join('merchandises', 'danger_transactions.mcht_id', '=', 'merchandises.id')
            ->join('transactions', 'danger_transactions.trans_id', '=', 'transactions.id')
            ->where('danger_transactions.brand_id', $request->user()->brand_id);
            
        $query = globalPGFilter($query, $request, 'transactions');
        $query = globalSalesFilter($query, $request, 'transactions');
        $query = globalAuthFilter($query, $request, 'transactions');

        $query = $query->where(function ($query) use ($search) {
            return $query->where('merchandises.mcht_name', 'like', "%$search%")
                ->orWhere('transactions.appr_num', 'like', "%$search%")
                ->orWhere('transactions.mid', 'like', "%$search%")
                ->orWhere('transactions.tid', 'like', "%$search%");
        });

        $data = $this->getIndexData($request, $query, 'danger_transactions.id', $cols, 'danger_transactions.created_at');
        return $this->response(0, $data);
    }

    /**
     * 확인/미확인 처리
     *
     */
    public function checked(Request $request, $id)
    {
        $validated = $request->validate(['checked'=>'required']);
        $res = $this->danger_transactions
            ->where('id', $id)
            ->update(['is_checked' => $request->checked]);
        return $this->response(1);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->danger_transactions
                ->where('id', $id)
                ->delete();
            return $this->response($res);
        }
        else
            return $this->response(951);
    }
}
