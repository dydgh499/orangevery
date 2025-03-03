<?php

namespace App\Models\Merchandise\VirtualAccount;

use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccountHistory extends Model
{
    use HasFactory, AttributeTrait;

    protected   $table      = 'virtual_account_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];
}
