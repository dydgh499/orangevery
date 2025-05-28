<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class CMSTransactionBooks extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'cms_transaction_books';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    /*
    protected $fillable = [
        'trans_seq_num' // 추가
    ];
    */
}
