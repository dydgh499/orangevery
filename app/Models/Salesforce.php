<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Models\Transaction;
use App\Models\UnderAutoSetting;
use App\Models\Log\SettleDeductSalesforce;
use App\Models\Log\SettleHistorySalesforce;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;

class Salesforce extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait;

    protected   $table      = 'salesforces';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = ['resident_num_front', 'resident_num_back'];
    protected   $hidden     = ['user_pw', 'resident_num'];

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

    // 자식 답변들
    public function childs()
    {
        return $this->hasMany(Salesforce::class, 'parent_id')
            ->with('childs')
            ->select(['id', 'parent_id', 'sales_fee', 'level', 'sales_name', 'is_able_under_modify']);
    }

    public function underAutoSettings()
    {
        return $this->hasMany(UnderAutoSetting::class, 'sales_id');
    }

    public function merchandises()
    {
        $level  = request()->level;
        $idx = globalLevelByIndex($level);
        $target_id = 'sales'.$idx.'_id';
        
        return $this->hasMany(Merchandise::class,  $target_id)
            ->select(['id', $target_id]); 
    }

    protected function MchtBatchFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => round($value * 100, 3),
        );
    }
}
