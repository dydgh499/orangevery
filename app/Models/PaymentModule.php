<?php

namespace App\Models;

use App\Models\Transaction;
use App\Models\Classification;
use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentModule extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'payment_modules';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];
    public function classifications()
    {
        return $this->belongsTo(Classification::class, 'terminal_id')
            ->where('is_delete', false)
            ->select(['id', 'name']);
    }

    public function payLimitAmount()
    {
        return $this->hasMany(Transaction::class, 'pmod_id')
            ->where('trx_dt', '>=', Carbon::now()->subYear()->toDateString())
            ->selectRaw('
                pmod_id,
                SUM(CASE WHEN trx_dt = CURDATE() THEN amount ELSE 0 END) as pay_day_amount,
                SUM(CASE WHEN MONTH(trx_dt) = MONTH(CURRENT_DATE()) AND YEAR(trx_dt) = YEAR(CURRENT_DATE()) THEN amount ELSE 0 END) as pay_month_amount,
                SUM(CASE WHEN YEAR(trx_dt) = YEAR(CURRENT_DATE()) THEN amount ELSE 0 END) as pay_year_amount
            ')
            ->groupBy('pmod_id'); 
    }

    // 단말기, 매출미달차감금 정산일인 것만
    public function scopeTerminalSettle($query, $level)
    {
        $settle_s_day   = (int)date('d', strtotime(request()->s_dt));
        $settle_e_day   = (int)date('d', strtotime(request()->e_dt));
        $settle_month   = date('Ym', strtotime(request()->e_dt)); //202310

        // 단말기 정산일이 정산일에 포함되는 것들 모두 조회
        return $query->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.brand_id', request()->user()->brand_id)
            ->where('payment_modules.comm_settle_day', '>=', $settle_s_day)
            ->where('payment_modules.comm_settle_day', '<=', $settle_e_day)
            ->where('payment_modules.last_settle_month', '<', $settle_month)
            ->where('payment_modules.comm_calc_level', $level)
            ->where('payment_modules.begin_dt', '<', request()->s_dt)
            ->where('payment_modules.is_delete', false);
    }

    public function scopeByTargetIds($query, $target_id)
    {
        return $query->distinct()
        ->groupby("merchandises.$target_id")
        ->pluck("merchandises.$target_id")
        ->all();
    }

    public function scopeNotUseLastMonth($query, $brand_id)
    {
        $s_dt = Carbon::now()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d 00:00:00');
        $e_dt = Carbon::now()->subMonthNoOverflow(1)->endOfMonth()->format('Y-m-d 23:59:59');

        $trans_pmod_ids = Transaction::where('brand_id', $brand_id)
            ->where('trx_at', '>=', $s_dt)
            ->where('trx_at', '<=', $e_dt)
            ->where('is_cancel', false)
            ->distinct()->pluck('pmod_id')->all();
        return $query->whereNotIn('payment_modules.id', $trans_pmod_ids);

    }

    protected function beginDt(): Attribute
    {
        return $this->dateAttribute();
    }

    protected function shipOutDt(): Attribute
    {
        return $this->dateAttribute();
    }

    protected function filterIssuers(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
        );
    }
}
