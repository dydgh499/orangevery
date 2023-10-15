<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class SettleHistoryMerchandise extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'settle_histories_merchandises';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
}
