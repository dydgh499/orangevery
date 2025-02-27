<?php
namespace App\Http\Controllers\Ablilty;

use App\Models\Salesforce;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Manager\Salesforce\UnderSalesforce;
use App\Http\Controllers\Manager\Salesforce\SalesforceOverlap;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Models\Merchandise;
use Carbon\Carbon;

class Ablilty
{
    static function isMyMerchandise($request, int $id)
    {
        return self::isMerchandise($request) && $request->user()->id === (int)$id;
    }
    
    static function isMerchandise($request)
    {
        return $request->user()->tokenCan(13) == false ? true : false;
    }

    static function salesAuthValidate($request, $id)
    {
        if($id === 0 && $request->user()->auth_level >= 1)
            return true;
        else if($id !== 0 && $request->user()->auth_level == 2)
            return true;
        else
            return false;
    }

    static function isMySalesforce($request, int $id)
    {
        return self::isSalesforce($request) && $request->user()->id === $id;
    }

    static function isSalesforce($request)
    {
        $cond_1 = $request->user()->tokenCan(13) == true;
        $cond_2 = $request->user()->tokenCan(35) == false;
        return $cond_1 && $cond_2;
    }

    static function isUnderSalesforce($request, $id)
    {
        if($request->user()->brand_id === 30 && self::isSalesforce($request))
            return true;
        else
        {
            $brand = BrandInfo::getBrandById($request->user()->brand_id);
            if($brand['pv_options']['paid']['sales_parent_structure'])
            {
                if(self::isSalesforce($request))
                {
                    $sales = Salesforce::where('id', $request->user()->id)->with(['childs'])->first();
                    $sales_ids = SalesforceOverlap::getAllChildIds([$sales->id], $sales->childs);    
                    return in_array($id, $sales_ids);
                }
                else
                    return false;
            }
            else
                return self::isSalesforce($request);
        }
    }

    static function isUnderMerchandise($request, $id)
    {
        if($request->user()->brand_id === 30 && self::isSalesforce($request))
            return true;
        else
        {
            $sales_filter = [
                'id'    => 'sales'.globalLevelByIndex($request->user()->level).'_id',
                'value' => $request->user()->id,
            ];
            $mcht_ids = Merchandise::where($sales_filter['id'], $sales_filter['value'])->pluck('id')->all();
            return in_array($id, $mcht_ids);    
        }
    }

    static function isOperator($request)
    {
        return $request->user()->tokenCan(35);
    }

    static function isMyOperator($request, int $id)
    {
        return self::isOperator($request) && $request->user()->id === $id;
    }

    static function isDevLogin($request)
    {
        return $request->user()->tokenCan(50) && self::isDevOffice($request);
    }

    static function isDevOffice($request)
    {
        $master_ips = json_decode(env('MASTER_IPS'), true);
        if(env('APP_ENV') === 'local')
            array_push($master_ips, '127.0.0.1');
        return in_array($request->ip(), $master_ips);
    }

    static function isBrandCheck($request, $brand_id, $is_dev_ok=false)
    {
        if($is_dev_ok)
        {
            $cond_1 = self::isDevLogin($request);
            $cond_2 = ($request->user()->brand_id !== $brand_id);
            if($cond_1 === false && $cond_2)
            {
                AbnormalConnection::tryParameterModulationApproach();
                return false;
            }
            else
                return true;
        }
        else
        {
            if($request->user()->brand_id !== $brand_id)
            {
                AbnormalConnection::tryParameterModulationApproach();
                return false;
            }
            else
                return true;
        }
    }
}
