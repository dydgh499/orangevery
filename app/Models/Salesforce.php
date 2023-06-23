<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use DateTimeInterface;
use App\Models\Transaction;
use App\Models\Logs\SettleDeductMerchandise;

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
            ->whereNull('sales'.$idx.'_settle_dt')
            ->select();
        $query = globalPGFilter($query, request());
        return $query;
    }
    
    function deducts()
    {
        return $this->hasMany(SettleDeductMerchandise::class, 'mcht_id')
                ->where('deduct_dt', request()->dt)
                ->select();
    }
}
