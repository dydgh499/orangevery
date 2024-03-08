<?php

namespace App\Http\Controllers\Log\SubBusinessRegistration;

use Carbon\Carbon;
use App\Models\Merchandise;
use App\Models\DifferentSettlementInfo;
use App\Models\Log\SubBusinessRegistration;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

/**
 * @group Sub-Busniess-Registration API
 *
 * 하위사업자등록 API 입니다.
 */
class SubBusinessRegistrationController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $sub_business_registrations;
    
    public function __construct(SubBusinessRegistration $sub_business_registrations)
    {
        $this->sub_business_registrations = $sub_business_registrations;    
    }

    public function index(IndexRequest $request)
    {
        $query = $this->sub_business_registrations->where('brand_id', $request->user()->brand_id);

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    private function getPGClass($pg_type)
    {
        $base_path = "App\Http\Controllers\Log\DifferenceSettlement\Manager\\";
        try
        {
            $pg_name = getPGType($pg_type);
            $path   = $base_path.$pg_name;            
            $pg     = new $path();
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

    public function registerAllMerchandise($pg_type)
    {
        $datas = [];
        $brand_id = 19;
        $created_at = date('Y-m-d H:i:s');

        $pg = $this->getPGClass($pg_type);
        $mchts = Merchandise::where('brand_id', $brand_id)
            ->where("business_num", '!=', '')
            ->groupBy('business_num')
            ->havingRaw('LENGTH(business_num) >= 10')
            ->get(['business_num']);

        foreach($mchts as $mcht)
        {
            foreach($pg->mcht_cards as $mcht_card_code => $mcht_card_name)
            {
                $datas[] = [
                    'brand_id' => $brand_id,
                    'business_num' => $mcht->business_num,
                    'pg_type'  => $pg_type,
                    'card_company_code' => $mcht_card_code,
                    'card_company_name' => $mcht_card_name,
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ];
            }
        }
        $res = $this->manyInsert($this->sub_business_registrations, $datas);
    }
}
