<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;
use DateTimeInterface;

class Operator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait;

    protected   $table        = 'operators';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    protected   $hidden = [
        'user_pw',
    ];
    protected   $casts = [
        'created_at' => "datetime:Y-m-d H:m:s",
        'updated_at' => "datetime:Y-m-d H:m:s",
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}
