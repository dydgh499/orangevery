<?php

namespace App\Models\Merchandise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\Models\AttributeTrait;

class PaymentModuleColumnApplyBook extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'payment_module_column_apply_books';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
}
