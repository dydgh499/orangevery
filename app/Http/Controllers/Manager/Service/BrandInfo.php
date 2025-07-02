<?php
namespace App\Http\Controllers\Manager\Service;

use App\Models\Brand;
use App\Models\Salesforce;
use Illuminate\Support\Facades\Redis;
use App\Models\Options\OvOptions;
use App\Http\Controllers\Ablilty\AbnormalConnection;

class BrandInfo
{
    static function hiddenInfo($brand)
    {
        $brand->makeHidden([
            'passbook_img',
            'id_img',
            'contract_img',
            'bsin_lic_img',
            'note',
            'is_delete',
            'created_at',
            'updated_at'
        ]);
    }


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
        if($brand === null || env('APP_ENV', 'local') === 'local')
        {
            $brand = Brand::where($key, $value)->first();
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
            $str_pv_options = json_encode($default['ov_options']);
            $default['ov_options'] = json_decode(json_encode(new OvOptions($str_pv_options)), true);
            return $default;
        }
    }
}
