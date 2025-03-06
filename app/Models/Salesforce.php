<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Salesforce\UnderAutoSetting;
use App\Models\Salesforce\SalesRecommenderCode;
use App\Models\Log\SettleDeductSalesforce;
use App\Models\Options\ThemeCSS;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;

class Salesforce extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait, EncryptDataTrait;

    protected   $table      = 'salesforces';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = ['resident_num_front', 'resident_num_back', 'is_2fa_use'];
    protected   $hidden     = ['user_pw', 'google_2fa_secret_key', 'resident_num'];

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

    // 자식 영업점들
    public function childs()
    {
        $query = $this->hasMany(Salesforce::class, 'parent_id')
            ->where('is_delete', false);
        if(request()->is_lock)
            $query = $query->where('is_lock', 1);

        return $query->with('childs')->select();
    }
    
    // 부모 영업점
    public function parent()
    {
        return $this->belongsTo(Salesforce::class, 'parent_id', 'id')
            ->where('is_delete', false)
            ->with('parent')
            ->select(['id', 'level', 'sales_name', 'parent_id', 'sales_fee']);
    }

    public function underAutoSettings()
    {
        return $this->hasMany(UnderAutoSetting::class, 'sales_id');
    }
    
    public function salesRecommenderCodes()
    {
        return $this->hasMany(SalesRecommenderCode::class, 'sales_id');
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
    
    protected function ThemeCss() : Attribute
    {
        return new Attribute(
            get: fn ($value) => new ThemeCSS($value),
        );
    }

    public function getIs2faUseAttribute()
    {
        return $this->google_2fa_secret_key ? true : false;
    }

    protected function SalesFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => round($value, 3),
        );
    }
}
