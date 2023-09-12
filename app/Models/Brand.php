<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;
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
}
