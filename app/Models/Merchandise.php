<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Transaction;
use App\Models\CollectWithdraw;
use App\Models\RegularCreditCard;

use App\Models\Log\SettleDeductMerchandise;
use App\Models\Log\RealtimeSendHistory;


use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;

class Merchandise extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, AttributeTrait;

    protected   $table      = 'merchandises';
    protected   $primaryKey = 'id';
    protected   $appends    = ['resident_num_front', 'resident_num_back'];
    protected   $hidden     = ['user_pw', 'resident_num'];
    protected   $guarded    = [];    
    protected   $feeFormatting = false;

    public function regularCreditCards()
    {
        return $this->hasMany(RegularCreditCard::class, 'mcht_id');
    }

    public function noSettles()
    {
        $cols = ['id', 'mcht_id', 'pmod_id', 'amount', 'mcht_settle_amount', 'mcht_settle_fee', 'hold_fee', 'is_cancel', 'created_at'];
        return $this->hasMany(Transaction::class, 'mcht_id')
            ->whereNull('mcht_settle_id')
            ->select($cols);
    }

    public function transactions()
    {
        $cols = ['id', 'mcht_id', 'pmod_id', 'amount', 'mcht_settle_amount', 'mcht_settle_fee', 'hold_fee', 'is_cancel', 'created_at'];
        $query = $this->hasMany(Transaction::class, 'mcht_id')->noSettlement('mcht_settle_id');

        // 실패건은 제외하고 조회
        if(request()->use_realtime_deposit == 1)
            $query = $query->whereNotIn('id', RealtimeSendHistory::onlyFailRealtime());

        return $query->select($cols);
    }

    public function collectWithdraws()
    {
        return $this->hasMany(CollectWithdraw::class, 'mcht_id')
            ->whereNull('mcht_settle_id')
            ->whereIn('result_code',['0000', '0050'])
            ->groupBy('mcht_id')
            ->where('withdraw_date', '>=', request()->s_dt)
            ->where('withdraw_date', '<=', request()->e_dt)
            ->selectRaw('mcht_id, SUM(withdraw_amount + withdraw_fee) as total_withdraw_amount');
    }

    public function deducts()
    {
        return $this->hasMany(SettleDeductMerchandise::class, 'mcht_id')
            ->where('brand_id', request()->user()->brand_id)
            ->where('deduct_dt', request()->e_dt)
            ->select();
    }
    
    protected function Sales5Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function Sales4Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function Sales3Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function Sales2Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function Sales1Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function Sales0Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function TrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
        );
    }

    protected function HoldFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->getFeeFormatting() ? round($value * 100, 3) : $value,
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
