<?php

namespace App\Http\Controllers\Manager\Settle\Difference;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DifferenceSettlement extends Controller
{    
    public function __construct()
    {
    }

    public function __invoke()
    {
        $brands = DB::table('brands')
            ->where('is_delete', false)
            ->where('is_use_different_settlement', true)
            ->get(['business_num', 'gid', 'id']);
        
        for ($i=0; $i<count($brands); $i++) 
        {
            $trans = DB::table('transactions')
                ->where('is_delete', false)
                ->where('brand_id', $brands[$i]->id)
                ->where('trx_dt', date('Y-m-d'))
                ->get();

            $pg_name = getPGType($brands[$i]->above_pg_type);
            try
            {
                $path   = "App\Http\Controllers\Manager\Settle\Difference\\".$pg_name;            
                $pg     = new $path();
                $pg->process($brands[$i]->business_num, $brands[$i]->gid, $trans);    
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

    public function response()
    {

    }
}
