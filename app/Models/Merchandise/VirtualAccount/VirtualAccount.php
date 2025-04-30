<?php

namespace App\Models\Merchandise\VirtualAccount;

use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasFactory, AttributeTrait;

    protected   $table      = 'virtual_accounts';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];
}

