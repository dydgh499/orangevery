<?php

namespace App\Http\Controllers\Manager\CodeGenerator;

use App\Http\Controllers\Manager\CodeGenerator\GeneratorInterface;
use App\Models\Merchandise\PaymentModule;
use App\Models\PaymentGateway;
use Carbon\Carbon;

class TidGenerator implements GeneratorInterface
{
    static public function getPayModuleByPgTypeTid($pg_type)
    {
        $pg_type = sprintf("%02d", $pg_type);
        $date = date('ym');

        $cur_month = Carbon::now()->startOfMonth();
        $next_month = $cur_month->copy()->addMonth(1)->startOfMonth()->format('Y-m-d 23:59:59');
        $cur_month = $cur_month->format('Y-m-d 00:00:00');

        $pay_modules = PaymentModule::where('is_delete', false)
            ->where('created_at', '>=', $cur_month)
            ->where('created_at', '<', $next_month)
            ->whereIn('pg_id', PaymentGateway::where('is_delete', false)->where('pg_type', $pg_type)->pluck('id')->all())
            ->get(['tid']);

        $pattern = '/^'.$pg_type.$date.'[0-9]{4}$/';
        return $pay_modules->filter(function($pay_module) use($pattern) {  
            return preg_match($pattern, $pay_module->tid) === 1;
        });
    }

    static public function getInitIdx($pg_type)
    {
        $cur_modules = self::getPayModuleByPgTypeTid($pg_type);
        if($cur_modules->count())
        {
            $idx = $cur_modules->map(function($pay_module) {
                return (int)substr($pay_module->tid, -4);
            })->max()+1;
        }
        else
            $idx = 0;
        return $idx;
    }

    static public function getNewTid($pg_type, $idx)
    {
        $pg_type = sprintf("%02d", $pg_type);
        $date = date('ym');
        return sprintf($pg_type.$date.'%04d', $idx);
    }

    static public function create($generate_code)
    {
        $init_idx = self::getInitIdx($generate_code);
        return self::getNewTid($generate_code, $init_idx);
    }

    static public function bulkCreate($generate_code, $create_count)
    {
        $new_tids = [];
        $init_idx = self::getInitIdx($generate_code);
        while (count($new_tids) < $create_count) 
        {
            $new_tids[] = self::getNewTid($generate_code, $init_idx);
            $init_idx++;
        }
        return $new_tids;
    }

}
