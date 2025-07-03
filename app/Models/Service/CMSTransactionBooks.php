<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class CMSTransactionHistories extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'cms_transaction_histories';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
}