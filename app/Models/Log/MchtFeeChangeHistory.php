<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MchtFeeChangeHistory extends Model
{
    use HasFactory;
    protected   $table      = 'mcht_fee_change_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'mcht', 
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    protected function BfTrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function AftTrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function BfHoldFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function AftHoldFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
}
