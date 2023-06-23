<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use DateTimeInterface;

class PaymentModule extends Model
{
    use HasFactory;
    protected   $table      = 'payment_modules';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'brand_id',
    ];
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
      
    protected function beginDt(): Attribute
    {
        if(env('APP_ENV') == 'production')
        {   //MS SQL 에서만 가능
            return Attribute::make(
                get: fn ($value) => Carbon::createFromFormat("M d Y H:i:s A", $value)->format('Y-m-d'),
            );
        }
        else
        {   //MY SQL 에서만 가능
            return Attribute::make(
                get: fn ($value) => Carbon::parse($value),
            );
        }
    }

    protected function shipOutDt(): Attribute
    {
        if(env('APP_ENV') == 'production')
        {   //MS SQL 에서만 가능
            return Attribute::make(
                get: fn ($value) => Carbon::createFromFormat("M d Y H:i:s A", $value)->format('Y-m-d'),
            );
        }
        else
        {   //MY SQL 에서만 가능
            return Attribute::make(
                get: fn ($value) => Carbon::parse($value),
            );
        }
    }
}
