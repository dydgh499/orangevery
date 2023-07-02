<?php

namespace App\Http\Traits\BeforeSystem;

trait BeforeSystemTrait
{
    public function getPaywellPrivacy($paywell, $users, $col='PK')
    {
        $pks = $users->pluck($col)->toArray();
        return $paywell->table('privacy')
            ->whereIn('USER_PK', $pks)
            ->get();
    }

    public function getPayveryFormat($paywell, $col='PK')
    {
        $items = array_map(function($obj) use($col) {
            $array = (array)$obj;
            unset($array[$col]);
            return (object)$array;
        }, $paywell);
        return json_decode(json_encode($items), true);
    }

    public function getPayvery($payvery_table, $brand_id, $updated_at)
    {
        return json_decode(
            json_encode(
                $payvery_table
                ->where('brand_id', $brand_id)
                ->where('updated_at', $updated_at)
                ->get()
            ), 
            true);
    }   

    public function connect($payvery, $paywell, $col='PK')
    {
        $connections = [];
        for ($i=0; $i < count($payvery) ; $i++) 
        { 
            $connections[$paywell[$i][$col]] = $payvery[$i]['id'];
        }
        return $connections;
    }
    
}
