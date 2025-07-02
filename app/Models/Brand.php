<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Options\OvOptions;
use App\Models\Options\ThemeCSS;
use App\Models\Service\OperatorIP;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;

class Brand extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'brands';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];

    protected function OvOptions() : Attribute
    {
        return new Attribute(
            get: fn ($value) => new OvOptions($value),
        );
    }
    protected function ThemeCss() : Attribute
    {
        return new Attribute(
            get: fn ($value) => new ThemeCSS($value),
        );
    }

    public function operatorIps()
    {
        return $this->hasMany(OperatorIP::class, 'brand_id')
            ->select();
    }
}
