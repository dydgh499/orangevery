<?php

namespace App\Http\Controllers\Log;

use App\Models\Transaction;
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

    public function __invoke()
    {
        $sub_query = DangerTransaction::query()->select('trans_id');
        // transactions 테이블에서 필요한 데이터 선택
        $transactions = Transaction::query()
            ->select('brand_id', 'mcht_id', DB::raw('MAX(id) AS trans_id'), 'module_type')
            ->whereNotIn('id', $sub_query)
            ->whereDate('trx_dt', '>=', now())
            ->groupBy('card_num', 'issuer', 'brand_id', 'mcht_id', 'is_cancel', 'trx_dt', 'module_type')
            ->havingRaw('COUNT(card_num) > 1 AND LENGTH(card_num) > 12')
            ->havingRaw('COUNT(issuer) > 1 AND LENGTH(issuer) > 1')
            ->havingRaw('COUNT(brand_id) > 1')
            ->having('is_cancel', false)
            ->get();
        // 선택된 각 트랜잭션에 대해 danger_transactions 테이블에 삽입
        foreach ($transactions as $transaction) {
            DangerTransaction::create([
                'brand_id' => $transaction->brand_id,
                'mcht_id' => $transaction->mcht_id,
                'trans_id' => $transaction->trans_id,
                'module_type' => $transaction->module_type,
                'danger_type' => 0,  // static value
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }
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

        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%");
            });    
        }

        $data = $this->getIndexData($request, $query, 'danger_transactions.id', $cols, 'transactions.created_at');
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
     * @urlParam id integer required 이상거래 PK
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
