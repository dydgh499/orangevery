<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Classification extends Model
{
    use HasFactory;
    protected   $table      = 'classifications';
    protected   $primaryKey = 'id';
    protected   $hidden     = ['is_delete'];
    protected $guarded      = [];
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}
