<?php
namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\Log\RealtimeSendHistory;

class TransactionFilter
{
    static public function getTotalCols($settle_amount, $group_key=null)
    {
        if($settle_amount === 'dev_settle_amount')
            $profit  = "(SUM($settle_amount) + SUM(dev_realtime_settle_amount)) AS profit";
        else
            $profit = "SUM($settle_amount) AS profit";

        $cols = [
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount"),
            DB::raw("SUM(is_cancel = 0) AS appr_count"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount"),
            DB::raw("SUM(is_cancel = 1) AS cxl_count"),
            DB::raw($profit),
        ];
        if($group_key)
            array_push($cols, $group_key);
        return $cols;
    }
    
    static public function date($request, $query)
    {
        if($request->s_dt && $request->e_dt)
        {   // 영업점, 가맹점 정산이력 추출이 아닐때
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
        $res = ['page'=>$page, 'page_size'=>$page_size];

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
    

    static public function option($query, $request)
    {
        if($request->only_cancel)
            $query = $query->where('transactions.is_cancel', true);
        if($request->mcht_settle_id)
            $query = $query->where('transactions.mcht_settle_id', $request->mcht_settle_id);
        if($request->only_realtime_fail)
            $query->whereIn('transactions.id', RealtimeSendHistory::onlyFailRealtime());
        
        if($request->no_settlement)
        {
            [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
            $query = $query->whereNull("transactions.$target_settle_id");
        }

        for ($i=0; $i < 6; $i++) 
        {
            $col = 'sales'.$i.'_settle_id';
            if($request->has($col))
                $query = $query->where('transactions.'.$col, $request->input($col));
        }
        return $query;
    }

    static public function common($request)
    {
        $search = $request->input('search', '');
        $query = Transaction::where('brand_id', $request->user()->brand_id);
        $query = self::date($request, $query);
        $min   = $query->min('id');

        $query  = Transaction::join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->where('transactions.brand_id', $request->user()->brand_id);
        $query = self::date($request, $query);
        if($min)
            $query = $query->where('transactions.id', '>=', $min);
            
        $query = $query->globalFilter();
        if($search !== "")
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('transactions.buyer_phone', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('merchandises.resident_num', 'like', "%$search%")
                    ->orWhere('merchandises.business_num', 'like', "%$search%")
                    ->orWhere('payment_modules.note', '%like%', $search)
                    ->orWhere('transactions.trx_id', $search);
            });
        }
        if($request->issuer && $request->issuer !== '전체')
        {
            $query = $query->where('transactions.issuer', 'like', "%$request->issuer%");
        }
        return self::option($query ,$request);
    }
}
