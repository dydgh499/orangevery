<?php

namespace App\Http\Traits\Salesforce;
use App\Models\Merchandise;

trait BatchApplyTrait
{
    /**
     * 결제모듈 가져오기
     */
    private function payModuleBatch($request)
    {
        $sales_key = $this->getSalesKeys($request);
        $sales_id = 'merchandises.'.$sales_key['sales_id'];
        $query = $this->pay_modules
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id)
            ->where($sales_id, $request->id);
        if($request->pg_id)
            $query = $query->where('payment_modules.pg_id', $request->pg_id);
        return $query;
    }

    /**
     * 가맹점 가져오기
     */
    private function merchandiseBatch($request)
    {
        $sales_key = $this->getSalesKeys($request);
        $query = $this->merchandises->where('brand_id', $request->user()->brand_id)
            ->where($sales_key['sales_id'], $request->id);
        if($request->custom_filter_id)
            $query = $query->where('custom_id', $request->custom_filter_id);
        return $query;
    }

    /**
     * 영업점 pk, fee
     */
    private function getSalesKeys($request)
    {
        $idx  = globalLevelByIndex($request->level);
        $sales_key = [
            'sales_fee' => 'sales'.$idx.'_fee',
            'sales_id'  => 'sales'.$idx.'_id',
        ];
        return $sales_key;
    }

    /**
     * 영업점 수수료율 적용 포멧 목록
     */
    public function getSalesResource($request, $change_status)
    {
        $sales_key = $this->getSalesKeys($request);
        $aft_trx_fee = $request->sales_fee/100;
        $aft_sales_id = $request->id;

        $mchts = $this->merchandiseBatch($request)->get();
        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            $bf_trx_fee = $js_mcht[$sales_key['sales_fee']];
            $bf_sales_id = $mcht[$sales_key['sales_id']];            
            array_push($datas, [
                'brand_id' => $request->user()->brand_id,
                'mcht_id'   => $js_mcht['id'],
                'level'     => $request->level,
                'bf_trx_fee' => $bf_trx_fee,
                'aft_trx_fee' => $aft_trx_fee,
                'bf_sales_id' => $bf_sales_id,
                'aft_sales_id' => $aft_sales_id,
                'change_status' => $change_status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $datas;
    }

    /**
     * 영업점 수수료율 적용 포멧 목록
     */
    public function getMchtResource($request, $change_status)
    {
        $sales_key = $this->getSalesKeys($request);
        $aft_trx_fee = $request->mcht_fee/100;
        $aft_hold_fee = $request->hold_fee/100;

        $mchts = $this->merchandiseBatch($request)->get();
        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            $bf_trx_fee = $js_mcht[$sales_key['sales_fee']];
            $bf_sales_id = $mcht[$sales_key['sales_id']];            
            array_push($datas, [
                'brand_id' => $request->user()->brand_id,
                'mcht_id'   => $js_mcht['id'],
                'bf_trx_fee' => $js_mcht['trx_fee'],
                'aft_trx_fee' => $aft_trx_fee,
                'bf_hold_fee' => $js_mcht['hold_fee'],
                'aft_hold_fee' => $aft_hold_fee,
                'change_status' => $change_status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $datas;
    }
}
