<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Carbon\Carbon;
use DateTimeInterface;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

class Merchandise extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait;

    protected   $table        = 'merchandises';
    protected   $primaryKey   = 'id';

    protected $hidden = [
        'user_pw',
    ];
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:m:s",
        'updated_at' => "datetime:Y-m-d H:m:s",
    ];
    protected $guarded = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    protected function birthDate(): Attribute
    {   //mutator
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
