<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class RealtimeSendHistory extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'realtime_send_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = ['brand_id',];

    // 실시간 실패 거래건만 조회
    public function scopeOnlyFailRealtime($query)
    {
        return $query
            ->where('brand_id', request()->user()->brand_id)
            ->where('created_at', '>=',  request()->s_dt)
            ->where('created_at', '<=', request()->e_dt)
            ->groupBy('trans_id')
            ->havingRaw('SUM(CASE WHEN request_type = 6170 AND result_code = "0000" THEN 1 ELSE 0 END) = 0')
            ->pluck('trans_id')
            ->all();
    }
}
