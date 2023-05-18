<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTimeInterface;

class PaymentSection extends Model
{
    use HasFactory;
    protected   $table        = 'payment_sections';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
}
