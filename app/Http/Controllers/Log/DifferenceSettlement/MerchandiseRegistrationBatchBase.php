<?php
namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Models\Brand;
use App\Models\Service\DifferentSettlementInfo;
use App\Http\Controllers\Controller;

use App\Http\Traits\StoresTrait;

class MerchandiseRegistrationBatchBase extends Controller
{
    use StoresTrait;
    public $base_path = "App\Http\Controllers\Log\DifferenceSettlement\\Container\\";

    public function getUseDifferentSettlementBrands()
    {
        return Brand::join('different_settlement_infos', 'brands.id', '=', 'different_settlement_infos.brand_id')
            ->where('brands.is_delete', false)
            ->where('brands.use_different_settlement', true)
            ->where('different_settlement_infos.is_delete', false)
            ->get(['brands.business_num', 'different_settlement_infos.*']);
    }

    public function getPGClass($brand)
    {
        try
        {
            $pg_name = getPGType($brand->pg_type);
            $path   = $this->base_path.$pg_name;
            $pg     = new $path(json_decode(json_encode($brand), true));
        }
        catch(Exception $e)
        {   // pg사 발견못함
            $pg     = null;
            logging([
                    'message' => $e->getMessage(),
                    'brand' => json_decode(json_encode($brands[$i]), true),
                ],
                'PG사가 없습니다.'
            );
        }
        return $pg;
    }

}
