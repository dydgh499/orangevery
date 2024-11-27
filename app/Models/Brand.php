<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;
use App\Models\Transaction;
use App\Models\Service\OperatorIP;
use App\Models\Service\BeforeBrandInfo;
use App\Models\Service\DifferentSettlementInfo;

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

    public function operatorIps()
    {
        return $this->hasMany(OperatorIP::class, 'brand_id')
            ->select();
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
}
