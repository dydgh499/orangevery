<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Salesforce;
use App\Models\Transaction;
use App\Models\Log\SettleDeductMerchandise;
use App\Models\Log\SettleHistoryMerchandise;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;

class Merchandise extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait;

    protected   $table      = 'merchandises';
    protected   $primaryKey = 'id';
    protected   $appends    = [];
    protected   $hidden     = ['user_pw'];
    protected   $guarded    = [];    
    protected   $feeFormatting = false;

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'mcht_id')
            ->settleFilter()
            ->settleTransaction(request()->dt)
            ->select();
    }
    
    public function deducts()
    {
        return $this->hasMany(SettleDeductMerchandise::class, 'mcht_id')
            ->where('brand_id', request()->user()->brand_id)
            ->where('deduct_dt', request()->dt)
            ->select();
    }
    
    protected function Sales5Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function Sales4Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function Sales3Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function Sales2Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function Sales1Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function Sales0Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function TrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    protected function HoldFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? $value * 100 : $value,
        );
    }

    public function getFeeFormatting()
    {
        return $this->feeFormatting;
    }

    public function setFeeFormatting(bool $feeFormatting)
    {
        $this->feeFormatting = $feeFormatting;
    }
}
