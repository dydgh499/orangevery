<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class FailTransaction extends Model
{
    use HasFactory;
    protected   $table      = 'fail_transactions';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [        
        'trx_dttm',
    ];
    protected   $hidden     = [
        'mcht', 
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    public function getTrxDttmAttribute()
    {
        return $this->trx_dt." ".$this->trx_tm;
    }
}
