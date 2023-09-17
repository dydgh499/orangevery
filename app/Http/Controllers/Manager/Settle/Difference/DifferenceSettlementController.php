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
        $date   = Carbon::now();
        $brands = Brand::where('is_delete', false)
            ->where('is_use_different_settlement', true)
            ->get(['business_num', 'gid', 'id', 'above_pg_type']);
        
        for ($i=0; $i<count($brands); $i++)
        {
            $trans = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                ->where('is_delete', false)
                ->where('brand_id', $brands[$i]->id)
                ->where('trx_dt', date('Y-m-d'))
                ->get(['transactions.*', 'merchandises.business_num']);
            try
            {
                $pg_name = getPGType($brands[$i]->above_pg_type);
                $path   = "App\Http\Controllers\Manager\Settle\Difference\\".$pg_name;            
                $pg     = new $path();
                $pg->request($date, $brands[$i]->business_num, $brands[$i]->gid, $trans);    
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
