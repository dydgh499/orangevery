<?php

namespace App\Models\Merchandise;

use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalApiEnableIp extends Model
{
    use HasFactory, AttributeTrait;

    protected   $table      = 'external_api_enable_ips';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];
}
