<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class FailTransaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'fail_transactions';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [        
        'trx_dttm',
    ];
    protected   $hidden     = [
        'mcht', 
    ];

    public function getTrxDttmAttribute()
    {
        return $this->trx_dt." ".$this->trx_tm;
    }
}
