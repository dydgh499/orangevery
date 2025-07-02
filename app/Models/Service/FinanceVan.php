<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FinanceVan extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'finance_vans';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];

}
