<?php

namespace App\Models\Withdraws;

use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccountNotiUrl extends Model
{
    use HasFactory, AttributeTrait;

    protected   $table      = 'virtual_account_noti_urls';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];
}
