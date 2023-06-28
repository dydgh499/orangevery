<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use DateTimeInterface;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Transaction;
use App\Models\Logs\SettleDeductSalesforce;
use App\Models\Logs\SettleHistorySalesforce;

class Salesforce extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait;

    protected   $table          = 'salesforces';
    protected   $primaryKey     = 'id';
    protected   $guarded        = [];
    protected   $hidden = [
        'user_pw',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    public function transactions()
    {
        $idx = globalLevelByIndex(request()->level);        
        $query = $this->hasMany(Transaction::class, 'sales'.$idx."_id")
            ->where('brand_id', request()->user()->brand_id)
            ->whereNull('sales'.$idx.'_settle_id');

        $query = globalPGFilter($query, request());
        $query = globalSalesFilter($query, request());
        $query = globalAuthFilter($query, request());
        
        return $query->select();
    }
    
    public function deducts()
    {
        return $this->hasMany(SettleDeductSalesforce::class, 'sales_id')
            ->where('brand_id', request()->user()->brand_id)
            ->where('deduct_dt', request()->dt)
            ->select();
    }
}
