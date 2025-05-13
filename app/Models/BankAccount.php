<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class BankAccount extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'bank_accounts';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];

}
