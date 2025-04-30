<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class CollectWithdraw extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'collect_withdraws';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
}
