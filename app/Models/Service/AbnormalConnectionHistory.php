<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AbnormalConnectionHistory extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'abnormal_connection_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];

    protected function requestDetail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value, JSON_UNESCAPED_UNICODE),
        );
    }
}
