<?php
namespace App\Http\Controllers\Log\DifferenceSettlement;

use App\Http\Controllers\Log\DifferenceSettlement\MerchandiseRegistrationBatchBase;

use App\Models\Merchandise;
use App\Models\Log\SubBusinessRegistration;
use Illuminate\Support\Facades\DB;

class MerchandiseRegistrationBatch extends MerchandiseRegistrationBatchBase
{
    public function getMerchandiseRegistration($pg_type, $code)
    {
        return Merchandise::join('sub_business_registrations', 'merchandises.id', '=', 'sub_business_registrations.mcht_id')
            ->where('sub_business_registrations.pg_type', $pg_type)
            ->where('merchandises.is_delete', false)
            ->where('sub_business_registrations.registration_code', $code)
            ->get([
                'sub_business_registrations.id', 
                'merchandises.sector', 'merchandises.business_num',
                'merchandises.mcht_name','merchandises.addr',
                'merchandises.nick_name','merchandises.phone_num',
                'merchandises.email','merchandises.website_url',
            ]);
    }

    public function updateReqeustMerchandiseRegistration($groups)
    {
        foreach($groups as $code => $items)
        {
            DB::transaction(function () use($code, $items) {
                $ids = array_column($items, 'id');
                if(count($ids))
                {
                    SubBusinessRegistration::whereIn('id', $ids)
                        ->update([
                            'registration_code' => $items[0]['registration_code'],
                            'registration_msg'  => $items[0]['registration_msg'],
                            'registration_dt'   => $items[0]['registration_dt'],
                        ]);
                }
            });

        }

    }
}
