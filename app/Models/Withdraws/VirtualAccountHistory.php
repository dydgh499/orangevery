<?php
namespace App\Models\Withdraws;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;

class VirtualAccountHistory extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;

    protected   $table      = 'virtual_account_histories';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];

    public function withdraws()
    {
        return $this->hasMany(VirtualAccountWithdraw::class, 'va_history_id')
            ->orderBy('created_at', 'desc')
            ->select();
    }
    
    public function scopeFailIds($query, $request, $brand_id)
    {
        return $query
                ->where('brand_id', $brand_id)
                ->where('withdraw_status', 2)
                ->where('created_at', '>=', $request->s_dt)
                ->where('created_at', '<=', $request->e_dt)
                ->pluck('trx_id')
                ->all();
    }
}
