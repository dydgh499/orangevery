<?php

namespace App\Models\Salesforce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\Models\AttributeTrait;

class SalesforceColumnApplyBook extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'salesforce_column_apply_books';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
}
