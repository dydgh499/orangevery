<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class DangerTransaction extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'danger_transactions';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [
        'mcht', 
    ];
}
