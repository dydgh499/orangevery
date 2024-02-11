<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class DifferentSettlementInfo extends Model
{
    use HasFactory, AttributeTrait;
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];
}
