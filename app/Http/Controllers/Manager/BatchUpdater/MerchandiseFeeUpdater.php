<?php
namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\MerchandiseController;

use App\Models\Log\SfFeeApplyHistory;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;

use Illuminate\Support\Facades\DB;

class MerchandiseFeeUpdater
{
    /**
     * 영업점 수수료율 적용 포멧 목록
     */
    static private function getSalesResource($request, $change_status, $mchts)
    {
        $bf_sales_key = self::getSalesKeys($request->level);
        $aft_trx_fee = $request->sales_fee/100;
        $aft_sales_id = $request->sales_id;

        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            $bf_trx_fee  = $js_mcht[$bf_sales_key['sales_fee']];
            $bf_sales_id = $js_mcht[$bf_sales_key['sales_id']];            
            $datas[] = [
                'brand_id' => $request->user()->brand_id,
                'mcht_id'   => $js_mcht['id'],
                'level'     => $request->level,
                'apply_dt'  => $request->apply_dt,
                'bf_trx_fee' => $bf_trx_fee,
                'aft_trx_fee' => $aft_trx_fee,
                'bf_sales_id' => $bf_sales_id,
                'aft_sales_id' => $aft_sales_id,
                'change_status' => $change_status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        return $datas;
    }

    /**
     * 가맹점 수수료율 적용 포멧 목록
     */
    static private function getMchtResource($request, $change_status, $mchts)
    {
        $aft_trx_fee = $request->trx_fee/100;
        $aft_hold_fee = $request->hold_fee/100;

        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            $datas[] = [
                'brand_id' => $request->user()->brand_id,
                'mcht_id'   => $js_mcht['id'],
                'apply_dt'  => $request->apply_dt,
                'bf_trx_fee' => $js_mcht['trx_fee'],
                'aft_trx_fee' => $aft_trx_fee,
                'bf_hold_fee' => $js_mcht['hold_fee'],
                'aft_hold_fee' => $aft_hold_fee,
                'change_status' => $change_status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        return $datas;
    }

    /**
     * 영업점 pk, fee
     */
    static public function getSalesKeys($level)
    {
        $idx  = globalLevelByIndex($level);
        $sales_key = [
            'sales_fee' => 'sales'.$idx.'_fee',
            'sales_id'  => 'sales'.$idx.'_id',
        ];
        return $sales_key;
    }

    /**
     * 가맹점 이전 수수료
     */
    static public function getMchtBeforeFee($mcht)
    {
        return [
            'trx_fee' => $mcht->trx_fee,
            'hold_fee' => $mcht->hold_fee,
        ];
    }

    /**
     * 가맹점 이후 수수료
     */
    static public function getMchtAfterFee($request)
    {
        $aft_trx_fee = round($request->trx_fee/100, 7);
        $aft_hold_fee = round($request->hold_fee/100, 7);
        return [
            'trx_fee' => $aft_trx_fee,
            'hold_fee' => $aft_hold_fee,
        ];
    }

    /**
     * 상위 영업점 이전 수수료
     */
    static public function getSalesBeforeFee($mcht, $level)
    {
        $sales_key = self::getSalesKeys($level);
        $mcht = json_decode(json_encode($mcht), true);
        return [
            $sales_key['sales_id'] => $mcht[$sales_key['sales_id']],
            $sales_key['sales_fee'] => $mcht[$sales_key['sales_fee']],
        ];
    }

    /**
     * 상위 영업점 이후 수수료
     */
    static public function getSalesAfterFee($request)
    {
        $sales_key = self::getSalesKeys($request->level);
        $aft_sales_id = $request->sales_id;
        $aft_trx_fee = round($request->sales_fee/100, 7);
        return [
            $sales_key['sales_id'] => $aft_sales_id,
            $sales_key['sales_fee'] => $aft_trx_fee,
        ];
    }

    static public function apply($request, $user, $apply_type, $query)
    {
        // 0=즉시적용, 1=예약적용
        $change_status = $apply_type ? 0 : 1;
        if($user === 'merchandises')
        {
            $resources  = self::getMchtResource($request, $change_status, (clone $query)->get());
            $after      = self::getMchtAfterFee($request);
            $history_orm = new MchtFeeChangeHistory();
        }
        else
        {
            $resources  = self::getSalesResource($request, $change_status, (clone $query)->get());
            $after      = self::getSalesAfterFee($request);
            $history_orm = new SfFeeChangeHistory();
        }

        return DB::transaction(function () use($history_orm, $change_status, $user, $resources, $after, $query) {
            // 수수료율 변경이력 추가
            $res = resolve(MerchandiseController::class)->manyInsert($history_orm, $resources);
            if($res)
            {
                // 즉시변경일 시 수수료율 업데이트
                if($change_status)
                    $row = $query->update($after);
                return count($resources);
            }
            else
                return 0;
        });
    }

    /**
     * 영업점 일괄적용시 수수료 업데이트
     */    
    static public function SalesFeeHistoryUpdate($request)
    {
        $u_apply_res = SfFeeApplyHistory::where('sales_id', $request->sales_id)
            ->where(['is_delete' => false])
            ->update(['is_delete' => true]);
        $c_apply_res = SfFeeApplyHistory::create([
            'brand_id' => $request->user()->brand_id,
            'sales_id' => $request->sales_id,
            'trx_fee'  => round($request->sales_fee/100, 7),
        ]);
    }
}
