<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class SFFeeChangeHistory extends Model
{
    use HasFactory;
    protected   $table      = 'sf_fee_change_history';
    protected   $primaryKey = 'id';
    protected   $hidden     = ['is_use'];
    protected   $guarded    = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}
