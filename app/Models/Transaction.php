<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Models\Classification;
use App\Models\PaymentSection;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory;
    protected   $table      = 'transactions';
    protected   $primaryKey = 'id';
    protected   $appends    = [
        'sales0_name', 'sales1_name', 
        'sales2_name', 'sales3_name', 
        'sales4_name', 'sales5_name', 
        'ps_fee', 'pay_cond_fee', 
        'terminal_fee', 'custom_fee',
        'user_name', 'mcht_name',
    ];
    protected   $hidden     = [
        'sales0','sales1',
        'sales2','sales3',
        'sales4','sales5',
        'mcht',
    ];
    protected   $guarded    = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
    //sales
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
    
    public function mcht()
    {
        return $this->belongsTo(Merchandise::class, 'mcht_id')->select(['id', 'mcht_name', 'user_name']);
    }

    public function getMchtNameAttribute()
    {
        return $this->mcht ? $this->mcht->mcht_name : null;
    }

    public function getUserNameAttribute()
    {
        return $this->mcht ? $this->mcht->user_name : null;
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
    //
    protected function trxFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function holdFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function psFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function terminalFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function payCondFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }

    protected function customFee() : Attribute
    {
        return new Attribute(
            get: fn ($value) => number_format($value * 100, 3),
        );
    }
}
