<?php
namespace App\Http\Controllers\Manager\Service;

use App\Models\Brand;
use Illuminate\Support\Facades\Redis;
use App\Models\Options\PvOptions;

class BrandInfo
{
    static function getBrandByDNS($dns)
    {
        return self::getBrand($dns, 'dns', $dns);
    }

    static function getBrandById($brand_id)
    {
        $key_name = "brand-info-$brand_id";
        return self::getBrand($key_name, 'id', $brand_id);
    }
    
    static private function getBrand($key_name, $key, $value)
    {
        $brand = Redis::get($key_name);
        if($brand == null || env('APP_ENV', 'local') === 'local')
        {
            $brand = Brand::where($key, $value)->with(['beforeBrandInfos'])->first();
            if($brand)
            {
                Redis::set($key_name, json_encode($brand), 'EX', 300);
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
