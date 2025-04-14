<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Models\Transaction;
use App\Models\Log\DangerTransaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Danger-Transaction API
 *
 * 이상거래 API 입니다.
 */
class DangerTransController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $danger_transactions, $cols;

    public function __construct(DangerTransaction $danger_transactions)
    {
        $this->danger_transactions = $danger_transactions;        
        $this->cols = [
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
    }

    public function __invoke()
    {
        //pay_dupe_least 필터 적용
        $sub_query = DangerTransaction::select('trans_id');
        // transactions 테이블에서 필요한 데이터 선택
        $transactions = Transaction::join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->whereNotIn('transactions.id', $sub_query)
            ->whereDate('transactions.trx_dt', '>=', now())
            ->whereRaw('transactions.amount > payment_modules.pay_dupe_least * 10000')
            ->groupBy('transactions.card_num', 'transactions.issuer', 'transactions.brand_id', 'transactions.mcht_id', 'transactions.is_cancel', 'trx_dt', 'module_type')
            ->havingRaw('COUNT(transactions.card_num) > 1 AND LENGTH(transactions.card_num) > 12')
            ->havingRaw('COUNT(transactions.issuer) > 1 AND LENGTH(transactions.issuer) > 1')
            ->havingRaw('COUNT(transactions.brand_id) > 1')
            ->having('transactions.is_cancel', false)
            ->get(['transactions.brand_id', 'transactions.mcht_id', DB::raw('MAX(transactions.id) AS trans_id'), 'transactions.module_type']);
        // 선택된 각 트랜잭션에 대해 danger_transactions 테이블에 삽입
        $current = date('Y-m-d H:i:s');
        $dangers = [];
        foreach ($transactions as $transaction) {
            $dangers[] = [
                'brand_id' => $transaction->brand_id,
                'mcht_id' => $transaction->mcht_id,
                'trans_id' => $transaction->trans_id,
                'module_type' => $transaction->module_type,
                'danger_type' => 0,
                'created_at' => $current,
                'updated_at' => $current,
            ];
        }
        $res = $this->manyInsert(new DangerTransaction, $dangers);
        
        logging(['count' => count($dangers)], 'danger-transactions-scheduler');
    }

    private function commonSelect($request)
    {
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
        return $query;
    }

    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $query = $this->commonSelect($request);
        $data = $this->getIndexData($request, $query, 'danger_transactions.id', $this->cols, 'transactions.created_at');
        return $this->response(0, $data);
    }

    /**
     * 확인/미확인 처리
     *
     */
    public function checked(Request $request, int $id)
    {
        $validated = $request->validate(['checked'=>'required']);
        $res = $this->danger_transactions
            ->where('id', $id)
            ->update(['is_checked' => $request->checked]);
        return $this->response(1);
    }

    public function batchChecked(Request $request)
    {
        for ($i=0; $i < count($request->data); $i++)
        {
            $data = $request->data[$i];
            $res = $this->danger_transactions->where('id', $data['id'])->update(['is_checked' => $data['checked']]);
        }
        return $this->response(1);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 이상거래 PK
     */
    public function destroy(Request $request, int $id)
    {
        if(Ablilty::isOperator($request))
        {
            $res = $this->delete($this->danger_transactions->where('mcht_id', $id));
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
    }
}
