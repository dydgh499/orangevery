<?php

namespace App\Http\Traits\Salesforce;
use App\Models\Merchandise;

trait UnderSalesTrait
{
    public function getUnderSalesIds($request, $s_keys)
    {
        $idx = globalLevelByIndex($request->user()->level);
        return Merchandise::where('brand_id', $request->user()->brand_id)
            ->where('sales'.$idx.'_id', $request->user()->id)
            ->where('is_delete', false)
            ->get($s_keys);
    }

    public function getViewableSalesCols($request, $cols)
    {
        [$levels, $s_keys] = $this->getViewableSalesInfos($request);
        foreach($s_keys as $keys)
        {
            $key = explode('_', $keys);
            $cols[] = "merchandises.".$key[0]."_id";
            $cols[] = "merchandises.".$key[0]."_fee";
        }
        return $cols;
    }

    public function hiddenSalesInfos($request, $content)
    {
        $levels = [13, 15, 17, 20, 25, 30];
        for ($i=0; $i <count($levels); $i++) 
        { 
            if($request->user()->tokenCan($levels[$i]) === false)
            {
                $idx = globalLevelByIndex($levels[$i]);
                $key = 'sales'.$idx;
                $content[$key] = null;
                $content[$key."_id"] = null;
                $content[$key."_fee"] = null;
                $content[$key."_name"] = null;
                $content[$key."_settle_amount"] = null;
                $content[$key."_settle_id"] = null;
            }
        }
        return $content;
    }

    public function getViewableSalesInfos($request)
    {
        $levels = [];
        $s_keys = [];
        foreach([13,15,17,20,25,30] as $level)
        {
            if($request->user()->tokenCan($level))
                $levels[] = $level;
        }
        foreach($levels as $level)
        {
            $idx = globalLevelByIndex($level);
            $s_keys[] = 'sales'.$idx.'_id';
        }
        return [$levels, $s_keys];
    }
    
    private function salesClassFilter($request, $grouped, $levels)
    {
        $sales_keys = [];
        $my_level   = $request->user()->level;
        $my_id      = $request->user()->id;

        foreach($levels as $level)
        {
            $idx = globalLevelByIndex($level);
            $sales_keys[] = 'sales'.$idx.'_id';
        }

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
        $sales_ids = [];
        $selected_sales_infos = [];
        [$levels, $s_keys] = $this->getViewableSalesInfos($request);
        foreach($s_keys as $s_key)
        {
            $sales_id = $request->input($s_key, 0);
            if($sales_id)
            {
                $selected_sales_infos[] = [
                    'id' => $s_key,
                    'value' => $sales_id,
                ];
            }
        }

        if(isSalesforce($request))
        {
            $selected_sales_infos[] = [
                'id' => 'sales'.globalLevelByIndex($request->user()->level).'_id',
                'value' => $request->user()->id,
            ];
        }

        if(count($selected_sales_infos))
        {
            $sales_ids = array_merge($sales_ids, Merchandise::flatSalesIdByFilter($selected_sales_infos, $s_keys));
            if(isSalesforce($request) && count($sales_ids) == 0)
                $sales_ids[] = $request->user()->id;
            return $sales_ids;
        }
        else
            return [];
    }
}
