<?php

namespace App\Models\Merchandise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\Models\AttributeTrait;

class NotiUrlColumnApplyBook extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'noti_urls_column_apply_books';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
}
