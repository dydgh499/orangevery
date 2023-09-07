<?php

namespace App\Http\Traits\Salesforce;
use App\Models\Merchandise;

trait UnderSalesTrait
{
    public function getUnderSalesIds($request, $sales_keys)
    {
        $idx = globalLevelByIndex($request->user()->level);
        return Merchandise::where('brand_id', $request->user()->brand_id)
            ->where('sales'.$idx.'_id', $request->user()->id)
            ->where('is_delete', false)
            ->get($sales_keys);
    }

    public function getUnderSalesLevels($request)
    {
        $levels  = [];
        $_levels = [13,15,17,20,25,30];
        for ($i=0; $i<count($_levels); $i++)
        {
            if($request->user()->level >= $_levels[$i])
                array_push($levels, $_levels[$i]);
        }
        return $levels;
    }
    public function getUnderSalesKeys($levels)
    {
        $sales_keys = [];
        for($i=0; $i <count($levels); $i++) 
        {
            $idx = globalLevelByIndex($levels[$i]);
            array_push($sales_keys, 'sales'.$idx.'_id');
        }
        return $sales_keys;
    }
    
    private function salesClassFilter($request, $grouped, $levels)
    {
        $my_level = $request->user()->level;
        $my_id = $request->user()->id;

        $sales_keys = $this->getUnderSalesKeys($levels);
        $mchts = $this->getUnderSalesIds($request, $sales_keys);

        for($i=0; $i <count($sales_keys)-1; $i++) 
        {
            $key = $levels[$i];
            if(isset($grouped[$key]))
            {
                $ids = $mchts->pluck($sales_keys[$i])->values()->toArray();
                $grouped[$levels[$i]] = $grouped[$levels[$i]]->filter(function($sales) use($ids){
                    return array_search($sales->id, $ids) !== false;
                })->values();
            }
        }
        $grouped[$my_level] = $grouped[$my_level]->filter(function($sales) use($my_id) {
                return $sales->id == $my_id;
            })->values();
        return $grouped;
    }
}
