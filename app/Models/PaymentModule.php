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
