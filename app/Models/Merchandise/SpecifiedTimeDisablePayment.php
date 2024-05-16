<?php

namespace App\Models\Merchandise;

use App\Http\Traits\Models\AttributeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecifiedTimeDisablePayment extends Model
{
    use HasFactory, AttributeTrait;

    protected   $table      = 'specified_time_disable_payments';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];

    protected function disableSTm(): Attribute
    {
        return $this->dateAttribute();
    }

    protected function disableETm(): Attribute
    {
        return $this->dateAttribute();
    }
}
