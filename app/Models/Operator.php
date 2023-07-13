<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Traits\Models\AttributeTrait;

class Operator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait;

    protected   $table      = 'operators';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'user_pw',
    ];
}
