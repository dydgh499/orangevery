<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Transaction;
use App\Models\UnderAutoSetting;
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
        $cols = [
            'id',
            'is_cancel',
            'pmod_id',
            'amount',
            'sales'.$idx.'_id',
            'sales'.$idx.'_settle_amount',
        ];
        return $this->hasMany(Transaction::class, 'sales'.$idx."_id")
            ->noSettlement('sales'.$idx.'_settle_id')
            ->select($cols);
    }
    
    public function deducts()
    {
        return $this->hasMany(SettleDeductSalesforce::class, 'sales_id')
            ->where('brand_id', request()->user()->brand_id)
            ->where('deduct_dt', request()->e_dt)
            ->select();
    }

    public function underAutoSettings()
    {
        return $this->hasMany(UnderAutoSetting::class, 'sales_id');
    }
}
