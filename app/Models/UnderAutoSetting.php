<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class UnderAutoSetting extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'salesforces_under_auto_settings';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];

    protected function Sales5Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales4Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
    
    protected function Sales3Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales2Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales1Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales0Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
}
