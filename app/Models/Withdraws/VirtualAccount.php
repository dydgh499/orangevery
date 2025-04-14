<?php
namespace App\Models\Withdraws;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;

class VirtualAccount extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;

    protected   $table      = 'virtual_accounts';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];
}
