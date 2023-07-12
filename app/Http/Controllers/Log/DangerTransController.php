<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\DangerTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

class DangerTransController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $danger_transactions;

    public function __construct(DangerTransaction $danger_transactions)
    {
        $this->danger_transactions = $danger_transactions;
    }

    public function index(IndexRequest $request)
    {
        $cols = [
            'danger_transactions.*',
            'merchandises.mcht_name',
            'transactions.module_type',
            'transactions.item_name',
            'transactions.ord_num',
            'transactions.trx_id',
            'transactions.ori_trx_id',
            'transactions.mid',
            'transactions.tid',
            'transactions.issuer',
            'transactions.acquirer',
            'transactions.card_num',
            'transactions.appr_num',
            'transactions.trx_dt',
            'transactions.trx_tm',
            'transactions.amount',
            'transactions.buyer_name',
            'transactions.danger_type',
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
}
