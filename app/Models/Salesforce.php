<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Transaction;
use App\Models\Log\SettleDeductSalesforce;
use App\Models\Log\SettleHistorySalesforce;
use App\Http\Traits\Models\AttributeTrait;

class Salesforce extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait;

    protected   $table      = 'salesforces';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'user_pw',
    ];

    public function transactions()
    {
        $idx = globalLevelByIndex(request()->level);        
        return $this->hasMany(Transaction::class, 'sales'.$idx."_id")
            ->globalFilter()
            ->settleFilter('sales'.$idx.'_settle_id')
            ->settleTransaction()
            ->select();
    }
    
    public function deducts()
    {
        return $this->hasMany(SettleDeductSalesforce::class, 'sales_id')
            ->where('brand_id', request()->user()->brand_id)
            ->where('deduct_dt', request()->dt)
            ->select();
    }
}
