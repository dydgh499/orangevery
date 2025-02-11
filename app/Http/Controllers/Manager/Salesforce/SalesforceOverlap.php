<?php
namespace App\Http\Controllers\Manager\Salesforce;
use App\Models\Salesforce;
use App\Http\Controllers\Ablilty\Ablilty;

class SalesforceOverlap
{
    static private function getSalesforceChilds($request)
    {
        $parent_ids = [$request->user()->id];
        $my_idx = globalLevelByIndex($request->user()->level);                
        $dest_idx = globalLevelByIndex($request->level);
        $offset = $my_idx - $dest_idx;

        if($offset)
        {
            for ($i=0; $i < $offset; $i++) 
            {
                $is_dest_level = $i+1 === $offset;
                $sales_ids = Salesforce::whereIn('parent_id', $parent_ids)
                    ->where('is_delete', false)
                    ->pluck('id')
                    ->all();
                if($is_dest_level)
                    return $sales_ids;
                else
                    $parent_ids = $sales_ids;
            }    
            return [];
        }
        else
            return [$request->user()->id];
    }

    static public function OverlapSearch($request)
    {
        if(Ablilty::isSalesforce($request))
        {
            if(request()->level === null)
                $query = Salesforce::where('id', $request->user()->id);
            else
                $query = Salesforce::whereIn('id', self::getSalesforceChilds($request));
        }
        else if(Ablilty::isOperator($request))
        {
            $query = Salesforce::where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false);
            if(request()->level === null)
                $query = $query->where('level', 30);
            else
                $query = $query->where('level', $request->level);
        }
        else
            $query = null;

        if($request->search)
        {
            $search = $request->search;
            $query = $query->where(function($query) use ($search) {
                return $query->where('sales_name', 'like', "%$search%")
                    ->orWhere('user_name', 'like', "%$search%");
            });
        }
        if($request->is_lock)
            $query = $query->where('is_lock', 1);

        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;
        $total_count = (clone $query)->count();
        $content = $query
            ->offset($sp)
            ->limit($page_size)
            ->with(['childs'])
            ->get();
        return [$total_count, $content];
    }

    
    //
    static private function getParents($request)
    {
        $parents = [];
        $parent_id = $request->user()->parent_id;
        if($parent_id)
        {
            $idx = globalLevelByIndex($request->user()->level);
            for ($i=$idx; $i < 5; $i++)
            {
                $parent = Salesforce::where('id', $parent_id)
                    ->where('is_delete', false)
                    ->first([
                        'id', 'parent_id', 'sales_fee', 'level', 
                        'user_name','sales_name', 'is_able_under_modify', 
                        'mcht_batch_fee', 'business_num', 'is_lock', 'locked_at'
                    ]);
                if($parent)
                {
                    $parents[] = $parent;
                    if($parent->parent_id === null)
                        return $parents;
                    else
                        $parent_id = $parent->parent_id;
                }
                else
                    return $parents;
            }
            return $parents;
        }
        else
            return $parents;
    }

    static private function getRecursionChilds($data, $sales)
    {
        if($sales)
        {
            for ($i=0; $i <count($sales->childs); $i++) 
            {
                $data = self::getRecursionChilds($data, $sales->childs[$i]);
            }

            $data["level_".$sales->level][] = $sales;
            $sales->makeHidden(['childs']);
            return $data;    
        }
        else
            return $data;
    }

    static public function overlapClassification($request)
    {
        $data = [
            'level_13' => [], 'level_15' => [],
            'level_17' => [], 'level_20' => [], 
            'level_25' => [], 'level_30' => [],
        ];
        if(Ablilty::isSalesforce($request))
        {
            // parent
            $parents = self::getParents($request);
            foreach($parents as $parent)
            {
                $data["level_".$parent->level][] = $parent;
            }
            // child, self
            $sales = Salesforce::where('id', $request->user()->id)->with(['childs'])->first();
            $data = self::getRecursionChilds($data, $sales);
        }
        else if(Ablilty::isOperator($request))
        {
            $_sales = Salesforce::where('brand_id', $request->user()->brand_id)
                ->where('level', 30)
                ->with(['childs'])
                ->get(['id', 'parent_id', 'sales_fee', 'level', 'sales_name', 'is_able_under_modify', 'mcht_batch_fee']);

            foreach($_sales as $sales)
            {
                $data = self::getRecursionChilds($data, $sales);
            }
        }
        return $data;
    }
}
