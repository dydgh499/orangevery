<?php

namespace App\Http\Controllers\Manager\CodeGenerator;

use App\Http\Controllers\Manager\CodeGenerator\GeneratorInterface;
use App\Models\PaymentModule;
use Carbon\Carbon;

class MidGenerator implements GeneratorInterface
{
    static public function getNewMid($mid_code)
    {
        $num = sprintf("%06d", rand(0, 999999));
        return $mid_code.$num;
    }

    static public function getExistMids()
    {
        return PaymentModule::where('is_delete', false)
            ->pluck('mid')
            ->toArray();
    }

    static public function create($generate_code)
    {
        do {
            $mid = self::getNewMid($generate_code);
        }
        while(PaymentModule::where('mid', $mid)->exists());
        return $mid;
    }

    static public function bulkCreate($generate_code, $create_count)
    {
        $new_mids = [];
        $existing_mids = self::getExistMids();

        while (count($new_mids) < $create_count) 
        {
            $candidate_mid = self::getNewMid($generate_code);      
            // 발급한 mid 목록과, 기존 mid 목록에 없는 것만 추가
            if (!in_array($candidate_mid, $new_mids) && !in_array($candidate_mid, $existing_mids)) 
                $new_mids[] = $candidate_mid;
        }
        return $new_mids;
    }
}
