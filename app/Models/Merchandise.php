<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Carbon\Carbon;
use DateTimeInterface;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Salesforce;
use App\Models\Transaction;
use App\Models\Logs\SettleDeductMerchandise;
use App\Models\Logs\SettleHistoryMerchandise;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MchtOptions
{
    public $abnormal_trans_limit = 0;
    public $pay_day_limit = 0;
    public $pay_month_limit = 0;
    public $pay_year_limit = 0;
    public $pay_dupe_limit = 0;
    public $is_show_fee = true;

    public function __construct(string $pv_options)
    {
        $json = json_decode($pv_options, true);  
        $this->abnormal_trans_limit = isset($json['abnormal_trans_limit']) ? $json['abnormal_trans_limit'] : 0;
        $this->pay_day_limit = isset($json['pay_day_limit']) ? $json['pay_day_limit'] : 0;
        $this->pay_month_limit = isset($json['pay_month_limit']) ? $json['pay_month_limit'] : 0;
        $this->pay_year_limit = isset($json['pay_year_limit']) ? $json['pay_year_limit'] : 0;
        $this->pay_dupe_limit = isset($json['pay_dupe_limit']) ? $json['pay_dupe_limit'] : 0;
        $this->is_show_fee = isset($json['is_show_fee']) ? $json['is_show_fee'] : true;
    }
}

class Merchandise extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait;

    protected   $table        = 'merchandises';
    protected   $primaryKey   = 'id';
    protected   $appends    = ['sales0_name', 'sales1_name', 'sales2_name', 'sales3_name', 'sales4_name', 'sales5_name'];
    protected   $hidden     = [
        'user_pw',
        'location',
        'sales0','sales1','sales2','sales3','sales4','sales5',
    ];
    protected $guarded = [];

    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
    
    public function sales0()
    {
        return $this->belongsTo(Salesforce::class, 'sales0_id')->select(['id', 'nick_name']);
    }

    public function sales1()
    {
        return $this->belongsTo(Salesforce::class, 'sales1_id')->select(['id', 'nick_name']);
    }

    public function sales2()
    {
        return $this->belongsTo(Salesforce::class, 'sales2_id')->select(['id', 'nick_name']);
    }

    public function sales3()
    {
        return $this->belongsTo(Salesforce::class, 'sales3_id')->select(['id', 'nick_name']);
    }

    public function sales4()
    {
        return $this->belongsTo(Salesforce::class, 'sales4_id')->select(['id', 'nick_name']);
    }

    public function sales5()
    {
        return $this->belongsTo(Salesforce::class, 'sales5_id')->select(['id', 'nick_name']);
    }

    public function transactions()
    {
        $query = $this->hasMany(Transaction::class, 'mcht_id')
            ->where('brand_id', request()->user()->brand_id)
            ->whereNull('mcht_settle_id');
        $query = globalPGFilter($query, request());
        return $query->select();
    }
    
    public function deducts()
    {
        return $this->hasMany(SettleDeductMerchandise::class, 'mcht_id')
            ->where('brand_id', request()->user()->brand_id)
            ->where('deduct_dt', request()->dt)
            ->select();
    }

    public function getSales0NameAttribute()
    {
        return $this->sales0 ? $this->sales0->nick_name : null;
    }

    public function getSales1NameAttribute()
    {
        return $this->sales1 ? $this->sales1->nick_name : null;
    }

    public function getSales2NameAttribute()
    {
        return $this->sales2 ? $this->sales2->nick_name : null;
    }

    public function getSales3NameAttribute()
    {
        return $this->sales3 ? $this->sales3->nick_name : null;
    }
    
    public function getSales4NameAttribute()
    {
        return $this->sales4 ? $this->sales4->nick_name : null;
    }

    public function getSales5NameAttribute()
    {
        return $this->sales5 ? $this->sales5->nick_name : null;
    }

    protected function PvOptions() : Attribute
    {
        return new Attribute(
            get: fn ($value) => new MchtOptions($value),
        );
    }

    protected function Sales5Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales4Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales3Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales2Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales1Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function Sales0Fee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function TrxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function HoldFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
}
