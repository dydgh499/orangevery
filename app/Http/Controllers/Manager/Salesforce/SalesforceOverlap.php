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
                    ->pluck('id')->all();
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
}
