<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Brand extends Model
{
    use HasFactory;
    protected   $table        = 'brands';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden = [
        'options',
        'coupon_flag',
        'coupon_model_id',
        'stamp_flag',
        'stamp_max_size',
        'stamp_save_count',
        'guide_type',
        'map_marker_img',
        'point_flag',
        'point_min_amount',
        'point_rate',
    ];
    protected $casts = [
        'user_id' => "int",
        'mcht_id' => "int",
    ];

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
