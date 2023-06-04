<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentGateway;
use App\Models\Merchandise;
use DateTimeInterface;

class Complaint extends Model
{
    use HasFactory;
    protected   $table      = 'complaints';
    protected   $primaryKey = 'id';
    protected   $appends    = ['mcht_name', 'pg_name'];
    protected   $guarded    = [];
    protected   $hidden     = [
        'mcht', 
        'pg',
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
    public function mcht()
    {
        return $this->belongsTo(Merchandise::class, 'mcht_id')->select(['id', 'mcht_name']);
    }

    public function pg()
    {
        return $this->belongsTo(PaymentGateway::class, 'pg_id')->select(['id', 'pg_nm']);
    }

    public function getPgNameAttribute()
    {
        return $this->pg ? $this->pg->pg_nm : null;
    }
    public function getMchtNameAttribute()
    {
        return $this->mcht ? $this->mcht->mcht_name : null;
    }
}
