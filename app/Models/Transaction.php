<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\Models\AttributeTrait;

class Transaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'transactions';
    protected   $primaryKey = 'id';
    protected   $appends    = [];
    protected   $guarded    = [];
    protected   $feeFormatting = false;

    public function scopeGlobalFilter($query)
    {
        $query = $query->where('transactions.brand_id', request()->user()->brand_id);
        $query = globalPGFilter($query, request(), 'transactions');
        $query = globalAuthFilter($query, request(), 'transactions');
        return $query;
    }
}
