<?php
namespace App\Http\Controllers\Ablilty;

use App\Models\Brand;
use Illuminate\Support\Facades\Redis;
use App\Models\Options\OvOptions;

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
        if($brand === null || env('APP_ENV', 'local') === 'local')
        {
            $brand = Brand::where($key, $value)->first();
            if($brand)
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

    static public function isDeliveryBrand()
    {
        $brand = self::getBrandByDNS($_SERVER['HTTP_HOST']);
        return $brand['ov_options']['paid']['yn_delivery_mode'];
        /*
            배달대행 전산일 경우
                1. 한 브랜드 내에서 각기 다른 업체가 운영자로 존재
                2. 각 운영자는 자신들의 거래건, 이체건만 확인 가능해야함
        */
    }
}
