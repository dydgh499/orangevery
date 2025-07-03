<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service\CMSTransaction;

use App\Http\Traits\Models\AttributeTrait;

class Transaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'transactions';
    protected   $primaryKey = 'id';
    protected   $appends    = [];
    protected   $guarded    = [];

    public function cmsTransaction()
    {
        return $this->belongsTo(CMSTransaction::class, 'cms_id')->select();
    }

    public function cancel()
    {
        return $this->belongsTo(Transaction::class, 'trx_id', 'ori_trx_id')
            ->select();
    }
}
