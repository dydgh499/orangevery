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
use App\Http\Traits\Models\EncryptDataTrait;

class Operator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait, EncryptDataTrait;

    protected   $table      = 'operators';
    protected   $primaryKey = 'id';
    protected   $appends    = ['is_2fa_use'];
    protected   $guarded    = [];
    protected   $hidden     = [
        'user_pw',
        'google_2fa_secret_key',
    ];

    public function getIs2faUseAttribute()
    {
        return $this->google_2fa_secret_key ? true : false;
    }
}
