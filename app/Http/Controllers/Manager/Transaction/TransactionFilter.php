<?php
namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionFilter
{   
    static public function date($request, $query)
    {
        if($request->s_dt && $request->e_dt)
        {
            $s_dt = strlen($request->s_dt) === 10 ? date($request->s_dt." 00:00:00") : $request->s_dt;
            $e_dt = strlen($request->e_dt) === 10 ? date($request->e_dt." 23:59:59") : $request->e_dt;
            return $query
                ->whereRaw("transactions.trx_at >= ?", [$s_dt])
                ->whereRaw("transactions.trx_at <= ?", [$e_dt]);    
        }
        else
            return $query;
    }

    static public function pagenation($request, $_query, $cols, $order_by, $groupby)
    {
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp     = ($page - 1) * $page_size;
        $res = ['page' => $page, 'page_size' => $page_size];

        if($groupby)
        {
            $res['total'] = $_query->clone()->select($order_by, DB::raw('COUNT(*) as count'))
            ->groupBy($order_by)
            ->get()
            ->count();
        }
        else
            $res['total'] = $_query->count();

        $res['content'] = $_query->orderBy($order_by, 'desc')
            ->offset($sp)
            ->limit($page_size)
            ->get($cols);
        return $res;
    }
    


    static public function common($request)
    {
        $search = $request->input('search', '');
        $query  = Transaction::join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->where('transactions.brand_id', $request->user()->brand_id);
        $query = self::date($request, $query);
        $min   = (clone $query)->min('transactions.id');
        if($min)
            $query = $query->where('transactions.id', '>=', $min);            
        if($search !== "")
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('payment_modules.note', 'like', "%$search%");
            });
        }
        return $query;
    }
}
