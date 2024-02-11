<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Models\Log\SettleHistorySalesforceDeposit;

class SettleHistorySalesforce extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'settle_histories_salesforces';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];

    public function deposits()
    {
        return $this->hasMany(SettleHistorySalesforceDeposit::class, 'settle_hist_sales_id')
            ->where('is_delete', false)
            ->orderby('id', 'desc')
            ->select();
    }
}
