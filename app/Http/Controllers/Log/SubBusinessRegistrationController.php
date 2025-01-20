<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Log\DifferenceSettlement\MerchandiseRegistrationBatchBase;

use App\Models\Merchandise;
use App\Models\Log\SubBusinessRegistration;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

/**
 * @group Sub-Busniess-Registration API
 *
 * 하위사업자등록 API 입니다.
 */
class SubBusinessRegistrationController extends MerchandiseRegistrationBatchBase
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $sub_business_registrations;
    
    public function __construct(SubBusinessRegistration $sub_business_registrations)
    {
        $this->sub_business_registrations = $sub_business_registrations;    
    }

    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query = Merchandise::join('sub_business_registrations', 'merchandises.id', '=', 'sub_business_registrations.mcht_id')
                    ->where(function ($query) use ($search) {
                        return $query->where('merchandises.mcht_name', 'like', "%$search%")
                            ->orWhere('merchandises.business_num', 'like', "%$search%");
                    });
        if($request->status_code)
        {
            if((int)$request->status_code === 1)
                $query = $query->whereIn('sub_business_registrations.registration_code', ['00', '0000']);
            else if((int)$request->status_code === 2)
                $query = $query->whereNotIn('sub_business_registrations.registration_code', ['00', '0000', '50', '51']);
            else
                $query = $query->where('sub_business_registrations.registration_code', $request->status_code);
        }
        
        $data = $this->getIndexData($request, $query, 'sub_business_registrations.id', [
            'sub_business_registrations.*',
            'merchandises.business_num', 'merchandises.mcht_name'
        ], 'sub_business_registrations.created_at');
        return $this->response(0, $data);
    }

    public function registerAllMerchandise($pg_type)
    {
        $datas = [];
        $created_at = date('Y-m-d H:i:s');

        $pg = $this->getPGClass($pg_type);
        $mchts = Merchandise::where("business_num", '!=', '')
            ->where('id', 18)
            ->get();

        foreach($mchts as $mcht)
        {
            foreach($pg->mcht_cards as $mcht_card_code => $mcht_card_name)
            {
                $datas[] = [
                    'mcht_id' =>  $mchts->id,
                    'pg_type'  => $pg_type,
                    'card_company_code' => $mcht_card_code,
                    'card_company_name' => $mcht_card_name,
                    'registration_code' => '51',
                    'registration_msg'  => '재업로드',
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ];
            }
        }
        $res = $this->manyInsert($this->sub_business_registrations, $datas);
    }
}
