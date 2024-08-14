<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service\PaymentGateway;
use App\Models\Merchandise;
use App\Http\Traits\Models\AttributeTrait;

class Complaint extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'complaints';
    protected   $primaryKey = 'id';
    protected   $appends    = ['mcht_name', 'pg_name'];
    protected   $guarded    = [];
    protected   $hidden     = [
        'mcht', 
        'pg',
    ];
    
    public function mcht()
    {
        return $this->belongsTo(Merchandise::class, 'mcht_id')->select(['id', 'mcht_name']);
    }

    public function pg()
    {
        return $this->belongsTo(PaymentGateway::class, 'pg_id')->select(['id', 'pg_name']);
    }

    public function getPgNameAttribute()
    {
        return $this->pg ? $this->pg->pg_name : null;
    }
    public function getMchtNameAttribute()
    {
        return $this->mcht ? $this->mcht->mcht_name : null;
    }
}
