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
            if($_levels[$i] <= $request->user()->level)
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

    public function underSalesFilter($request)
    {
        if($request->input('level', false))
        {   //레벨이 선택되었다면
            $rq_idx = globalLevelByIndex($request->level);
            $s_keys = ['sales'.$rq_idx.'_id'];
        }
        else
        {   // 모두
            $levels = $this->getUnderSalesLevels($request);
            $s_keys = $this->getUnderSalesKeys($levels);
        }
        $sales = $this->getUnderSalesIds($request, $s_keys);
        $sales_ids = $sales->flatMap(function ($sale) use($s_keys) {
            $keys = [];
            foreach($s_keys as $s_key)
            {
                if($sale[$s_key] != 0)
                    $keys[] = $sale[$s_key];
            }
            return $keys;
        })->unique()->values();
        // 연관되어있는 가맹점이 없는 경우 본인 것만 출력
        if(count($sales_ids) == 0)
            $sales_ids[] = $request->user()->id;
        return $sales_ids;
    }
}
