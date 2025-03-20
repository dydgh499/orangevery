<?php

namespace App\Models\Salesforce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class SalesRecommenderCode extends Model
{
    use HasFactory;
    protected   $table = 'sales_recommender_codes';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
}
