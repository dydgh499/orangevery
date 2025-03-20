<?php

namespace App\Models\Salesforce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class SalesforceFeeTable extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table      = 'salesforce_fee_tables';
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $hidden     = [];


    static public function totalFee($parent_id)
    {
        $total_fee = 0;
        $table = self::where('sales1_id', $parent_id)->first();
        if($table)
        {
            $table = $table->toArray();
            for ($i=1; $i < 6 ; $i++) 
            { 
                $total_fee += $table['sales'.$i.'_fee'];
            }
        }
        return round($total_fee, 3);
    }
}
