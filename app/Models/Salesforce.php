<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Carbon\Carbon;
use DateTimeInterface;
use App\Http\Traits\AuthTrait;
use Laravel\Sanctum\HasApiTokens;
use Kalnoy\Nestedset\NodeTrait;
use App\Models\SFFeeChangeHistory;

class Salesforce extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthTrait, NodeTrait;

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
    
    public function parent()
    {
        return $this->belongsTo(Salesforce::class, 'parent_id');
    }

    public function mychildren()
    {
        return $this->hasMany(Salesforce::class, 'parent_id');
    }

    public function feeChangeHistories()
    {
        return $this->hasMany(SFFeeChangeHistory::class, 'sf_id');
    }
    
    public function children()
    {
        return $this->hasMany(Salesforce::class, 'parent_id')->with('children')
            ->select('id', 'parent_id', 'user_name', 'trx_fee', 'tax_type');
    }

    public function ancestors()
    {
        return $this->parent()->with('ancestors')
            ->select('id', 'parent_id', 'user_name', 'trx_fee', 'tax_type');
    }
}
