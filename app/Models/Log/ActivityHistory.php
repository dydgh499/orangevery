<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ActivityHistory extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'activity_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
}
