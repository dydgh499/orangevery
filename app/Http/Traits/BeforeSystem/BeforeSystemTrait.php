<?php

namespace App\Http\Traits\BeforeSystem;

trait BeforeSystemTrait
{
    public function getPaywellPrivacy($paywell, $users, $col='PK')
    {
        $pks = $users->pluck($col)->all();
        $chunks = array_chunk($pks, 999);
        $privacys = collect();
        foreach ($chunks as $chunk) {
            $privacy = $paywell->table('privacy')
                ->whereIn('USER_PK', $chunk)
                ->get();
            $privacys = $privacys->merge($privacy);
        }
        return $privacys;
    }

    public function getPayveryFormat($paywell, $col='PK')
    {
        $items = [];
        foreach ($paywell as $obj) 
        {
            $array = (array)$obj;
            unset($array[$col]);
            $items[] = $array;
        }
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
            $key   = $paywell[$i][$col];
            $value = $payvery[$i]['id'];
            $connections[$key] = $value;
        }
        return $connections;
    }
    
}
