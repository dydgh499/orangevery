<?php

namespace App\Models\Merchandise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\Models\AttributeTrait;

class PayWindow extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'pay_windows';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
}
