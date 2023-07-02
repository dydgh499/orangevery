<?php

namespace App\Http\Traits\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use DateTimeInterface;
use Carbon\Carbon;

trait AttributeTrait
{
    protected function dateAttribute()
    {
        return Attribute::make(
            get: fn ($value) => $value != null ? Carbon::parse($value)->format('Y-m-d') : $value,
        );
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}
