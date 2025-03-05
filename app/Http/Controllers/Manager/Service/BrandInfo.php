<?php
namespace App\Http\Controllers\Manager\Service;

use App\Models\Brand;
use App\Models\Salesforce;
use Illuminate\Support\Facades\Redis;
use App\Models\Options\PvOptions;

class BrandInfo
{
    static function hiddenInfo($brand)
    {
        $brand->makeHidden([
            'passbook_img',
            'id_img',
            'contract_img',
            'bsin_lic_img',
            'seal_img',
            'pvcy_rep_name',
            'note',
            'dev_fee',
            'deposit_day',
            'deposit_amount',
            'extra_deposit_amount',
            'curr_deposit_amount',
            'last_dpst_at',
            'is_transfer',
            'p2p_app_dns',
            'is_delete',
            'created_at',
            'updated_at'
        ]);
    }

    static function getSalesByDns($dns)
    {
        $sales = Salesforce::where('dns', $dns)->first();
        if($sales)
        {
            $brand = self::getBrandById($sales->brand_id);
            if($brand)
            {
                $brand['name']    = $sales->name;
                $brand['dns']     = $sales->dns;
                $brand['logo_img']    = $sales->logo_img;
                $brand['favicon_img'] = $sales->favicon_img;
                $brand['og_img']      = $sales->og_img;
                $brand['login_img']   = $sales->login_img;
                $brand['og_description']    = $sales->og_description;
                $brand['theme_css']         = $sales->theme_css;
                return $brand;    
            }
        }
        return [];
    }

    static function getBrandByDNS($dns)
    {
        $brand = self::getBrand($dns, 'dns', $dns);
        if(count($brand) === 0)
            return self::getSalesByDns($dns);
        else
            return $brand;
    }

    static function getBrandById($brand_id)
    {
        $key_name = "brand-info-$brand_id";
        return self::getBrand($key_name, 'id', $brand_id);
    }
    
    static private function getBrand($key_name, $key, $value)
    {
        $brand = Redis::get($key_name);
        if($brand === null || env('APP_ENV', 'local') === 'local')
        {
            $brand = Brand::where($key, $value)->with(['beforeBrandInfos'])->first();
            if($brand)
            {
                self::hiddenInfo($brand);
                Redis::set($key_name, json_encode($brand), 'EX', 600);
                return json_decode(json_encode($brand), true);
            }
            else
                return [];
        }
        else
        {
            $default = json_decode($brand, true);
            $str_pv_options = json_encode($default['pv_options']);
            $default['pv_options'] = json_decode(json_encode(new PvOptions($str_pv_options)), true);
            return $default;
        }
    }
}
