<?php
namespace App\Http\Traits;
use App\Models\Operator;
use App\Models\Salesforce;
use App\Models\Merchandise;
use Illuminate\Support\Facades\DB;

trait StoresTrait
{
    public function manyInsert($orm, $datas)
    {
        $pieces = [];
        $piece = [];
        for($i=0; $i<count($datas); $i++)
        {
            array_push($piece, $datas[$i]);
            if(count($piece)%900 == 0)
            {
                array_push($pieces, $piece);
                $piece = [];
            }
        }
        if(count($piece) > 0)
            array_push($pieces, $piece);

        return DB::transaction(function () use($orm, $pieces) {
            for($i=0; $i<count($pieces); $i++)
            {
                $res = $orm->insert($pieces[$i]);
                if(!$res)
                    return false;
            }
            return true;
        });
    }

    public function isExistUserName($brand_id, $user_name)
    {
        $checkExist = function($orm, $brand_id, $user_name) {
            return $orm->where('brand_id', $brand_id)
                ->where('is_delete', false)
                ->where('user_name', $user_name)
                ->select('user_name');
        };
        $mcht = $checkExist(new Merchandise, $brand_id, $user_name);
        $sale = $checkExist(new Salesforce, $brand_id, $user_name);
        $oper = $checkExist(new Operator, $brand_id, $user_name);

        return $mcht->unionAll($sale)->unionAll($oper)->exists();
    }

    public function isExistBulkUserName($brand_id, $user_names)
    {
        $checkExist = function($orm, $brand_id, $user_names) {
            return $orm->where('brand_id', $brand_id)
                    ->where('is_delete', false)
                    ->whereIn('user_name', $user_names)
                    ->select('user_name');
        };
        
        $mcht = $checkExist(new Merchandise, $brand_id, $user_names);
        $sale = $checkExist(new Salesforce, $brand_id, $user_names);
        $oper = $checkExist(new Operator, $brand_id, $user_names);

        return $mcht->unionAll($sale)->unionAll($oper)->pluck('user_name')->toArray();
    }

    public function isExistMutual($orm, $brand_id, $col, $mutual)
    {
        if($brand_id === 30)
            return false;
        else
        {
            return $orm
            ->where('brand_id', $brand_id)
            ->where('is_delete', false)
            ->where($col, $mutual)
            ->exists();
        }
    }

    public function isExistBulkMutual($orm, $brand_id, $col, $mutuals)
    {
        if($brand_id === 30)
            return [];
        else
        {
            return $orm
            ->where('brand_id', $brand_id)
            ->where('is_delete', false)
            ->whereIn($col, $mutuals)
            ->pluck($col)
            ->toArray();
        }
    }
}
