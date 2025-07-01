<?php
namespace App\Http\Controllers\Manager\Salesforce;

use App\Models\Salesforce;
use App\Models\Merchandise;
use App\Http\Controllers\Ablilty\Ablilty;

class UnderSalesforce
{
    // 레벨에 따라 확인 가능한 하위 영업라인 정보 세팅
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
}
