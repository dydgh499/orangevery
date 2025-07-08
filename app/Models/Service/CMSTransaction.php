<?php

namespace App\Models\Service;

use App\Models\Service\CMSTransactionHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class CMSTransaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'cms_transactions';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];

    public function withdraws()
    {
        return $this->hasMany(CMSTransactionHistory::class, 'ct_id')
        ->orderBy('cms_transaction_histories.created_at', 'desc')
        ->join('cms_transactions', 'cms_transactions.id', '=', 'cms_transaction_histories.ct_id')
        ->select();
    }
}
