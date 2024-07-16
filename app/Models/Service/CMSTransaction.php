<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class CMSTransaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'cms_transactions';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
}
