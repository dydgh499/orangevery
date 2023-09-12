<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class OperatorHistory extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'operator_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    public $cols = [
        'operators.nick_name',
        'operators.profile_img',
        'operator_histories.id',
        'operator_histories.history_type',
        'operator_histories.history_title',
        'operator_histories.history_target',
        'operator_histories.created_at',
    ];
}
