<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Carbon\Carbon;
use DateTimeInterface;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

class Salesforce extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait;

    protected   $table          = 'salesforces';
    protected   $primaryKey     = 'id';
    protected   $guarded        = [];
    protected   $hidden = [
        'user_pw',
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}
