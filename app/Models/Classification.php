<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class Classification extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'classifications';
    protected   $primaryKey = 'id';
    protected   $hidden     = ['is_delete'];
    protected $guarded      = [];    
}
