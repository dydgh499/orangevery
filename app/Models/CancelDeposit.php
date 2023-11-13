<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class CancelDeposit extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'cancel_deposits';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
}
