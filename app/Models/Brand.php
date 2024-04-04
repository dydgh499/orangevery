<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;
use App\Models\Transaction;
use App\Models\BeforeBrandInfo;
use App\Models\DifferentSettlementInfo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;

class Brand extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'brands';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];

    protected function PvOptions() : Attribute
    {
        return new Attribute(
            get: fn ($value) => new PvOptions($value),
        );
    }
    protected function ThemeCss() : Attribute
    {
        return new Attribute(
            get: fn ($value) => new ThemeCSS($value),
        );
    }
    protected function devFee() : Attribute
    {
        return Attribute::make(
            get: fn ($value) =>  round($value * 100, 3),
        );
    }

    protected function isUseDifferentSettlement() : Attribute
    {
        return Attribute::make(
            get: fn ($value) => (boolean)$value,
        );
    }

    public function beforeBrandInfos()
    {
        return $this->hasMany(BeforeBrandInfo::class, 'brand_id')
            ->where('is_delete', false)
            ->select();
    }

    public function differentSettlementInfos()
    {
        return $this->hasMany(DifferentSettlementInfo::class, 'brand_id')
            ->where('is_delete', false)
            ->select();
    }
    
    public function devAmount()
    {
        request()->merge(['level'=> 50]);
        $s_dt = Carbon::now()->copy()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d 00:00:00');
        $e_dt = Carbon::now()->copy()->subMonthNoOverflow(1)->endOfMonth()->format('Y-m-d 23:59:59');

        return $this->hasMany(Transaction::class, 'brand_id')
            ->where('transactions.trx_at', '>=', $s_dt)
            ->where('transactions.trx_at', '<=', $e_dt)
            ->selectRaw('brand_id, SUM(dev_realtime_settle_amount + dev_settle_amount) as dev_percent_amount')
            ->groupBy('brand_id');
    }
}
