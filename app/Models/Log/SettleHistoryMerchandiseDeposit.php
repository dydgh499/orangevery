<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class SettleHistoryMerchandiseDeposit extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'settle_histories_merchandises_deposits';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
}
