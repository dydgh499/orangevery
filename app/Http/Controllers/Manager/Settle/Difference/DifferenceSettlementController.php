<?php

namespace App\Http\Controllers\Manager\Settle\Difference;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DifferenceSettlementController extends Controller
{    
    public function __construct()
    {

    }

    public function __invoke()
    {
        $date       = Carbon::now()->subDay(1);
        $str_date   = $date->format('Y-m-d');
        $brands = Brand::where('is_delete', false)
            ->where('is_use_different_settlement', true)
            ->get(['business_num', 'rep_mcht_id', 'id', 'above_pg_type']);
        
        for ($i=0; $i<count($brands); $i++)
        {
            $pg_name = getPGType($brands[$i]->above_pg_type);
            $trans   = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                ->join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
                ->where('transactions.is_delete', false)
                ->where('merchandises.is_delete', false)
                ->where('payment_gateways.pg_type', $brands[$i]->above_pg_type)
                ->where('transactions.brand_id', $brands[$i]->id)
                ->where('transactions.trx_dt', $str_date)                
                ->get(['transactions.*', 'merchandises.business_num']);
            try
            {
                $path   = "App\Http\Controllers\Manager\Settle\Difference\\".$pg_name;            
                $pg     = new $path();
                $pg->request($date, $brands[$i]->business_num, $brands[$i]->rep_mcht_id, $trans);    
            }
            catch(Exception $e)
            {   // pg사 발견못함
                logging([
                        'message' => $e->getMessage(),
                        'brand' => json_decode(json_encode($brands[$i]), true),
                    ],
                    'PG사가 없습니다.'
                );
            }
        }
    }

    public function response(Request $request)
    {
        $validated = $request->validate(['dt'=>'required']);

    }
}
