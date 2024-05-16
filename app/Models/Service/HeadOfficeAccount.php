<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class HeadOfficeAccount extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'head_office_accounts';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
}
