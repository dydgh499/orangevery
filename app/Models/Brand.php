<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;
use Illuminate\Database\Eloquent\Casts\Attribute;
use DateTimeInterface;

class Brand extends Model
{
    use HasFactory;
    protected   $table        = 'brands';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden = [];
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

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
}
