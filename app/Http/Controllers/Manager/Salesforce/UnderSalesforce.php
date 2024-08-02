<?php
namespace App\Http\Controllers\Manager\Salesforce;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Http\Controllers\Ablilty\Ablilty;
class UnderSalesforce
{
    static private function getMappingMcht($request, $sales_filters, $s_keys)
    {
        $query = Merchandise::where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false);
        foreach($sales_filters as $sales_filter)
        {
            $query = $query->where($sales_filter['id'], $sales_filter['value']);
        }
        return $query->get($s_keys);
    }

    // 영업점 필터가 있는지 선택되었는지 검사
    static public function getSelectedSalesFilter($request)
    {
        $sales_filters = [];
        $s_keys = self::getViewableSalesIds($request);
        foreach($s_keys as $s_key)
        {
            $sales_id = $request->input($s_key, 0);
            if($sales_id)
            {
                $sales_filters[] = [
                    'id' => $s_key,
                    'value' => $sales_id,
                ];
            }
        }
        if(Ablilty::isSalesforce($request))
        {   // 로그인 계정이 영업점이라면 자동으로 본인 선택
            $sales_filters[] = [
                'id' => 'sales'.globalLevelByIndex($request->user()->level).'_id',
                'value' => $request->user()->id,
            ];
        }
        return $sales_filters;
    }

    // 가맹점 목록에 조회될 영업점 필터
    static private function getMappingMchtFilter($request)
    {
        $s_keys = self::getViewableSalesIds($request);
        $sales_filters = self::getSelectedSalesFilter($request);
        return self::getMappingMcht($request, $sales_filters, $s_keys);
    }

    static public function getViewableSalesCols($request, $cols)
    {
        $s_keys = self::getViewableSalesIds($request);
        foreach($s_keys as $keys)
        {
            $key = explode('_', $keys);
            $cols[] = "merchandises.".$key[0]."_id";
            $cols[] = "merchandises.".$key[0]."_fee";
        }
        return $cols;
    }

    // 레벨에 따라 확인 가능한 하위 영업점 정보 세팅
    static public function setViewableSalesInfos($request, $content)
    {
        foreach([13,15,17,20,25,30] as $level)
        {
            if($request->user()->tokenCan($level) === false)
            {
                $idx = globalLevelByIndex($level);
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

    // 레벨에 따라 확인 가능한 하위 영업점 ID
    static public function getViewableSalesIds($request)
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
        return $s_keys;
    }

    // 레벨에 따라 확인 가능한 하위 영업점 level
    static public function getViewableSalesLevels($request)
    {
        $levels = [];
        foreach([13,15,17,20,25,30] as $level)
        {
            if($request->user()->tokenCan($level))
                $levels[] = $level;
        }
        return $levels;
    }


    static public function getSalesIds($request)
    {
        $s_keys = self::getViewableSalesIds($request);
        $mchts = self::getMappingMchtFilter($request);
        if(count($mchts) > 0)
        {
            $sales_ids = $mchts->flatMap(function ($mcht) use($s_keys) {
                $keys = [];
                foreach($s_keys as $s_key)
                {
                    if($mcht[$s_key] !== 0)
                        $keys[] = $mcht[$s_key];
                }
                return $keys;
            })->unique()->values()->all();    
            // 하위에 매핑된 가맹점이 하나도 없을 시 자신것 이라도 추가
            if(Ablilty::isSalesforce($request) && count($sales_ids) === 0)
                $sales_ids[] = $request->user()->id;
            return $sales_ids; 
        }
        else
            return [];
    }
}
