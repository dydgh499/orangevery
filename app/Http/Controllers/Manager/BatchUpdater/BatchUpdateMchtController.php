<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Models\Merchandise;
use App\Models\NotiUrl;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Log\SfFeeApplyHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Merchandise-Batch-Updater API
 *
 * 가맹점 일괄 업데이트 group 입니다.
 */
class BatchUpdateMchtController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $merchandises;

    public function __construct(Merchandise $merchandises)
    {
        $this->merchandises = $merchandises;
    }


    /**
     * 선택된 가맹점 가져오기
     */
    private function merchandiseBatch($request)
    {
        if(count($request->selected_idxs) == 0 && ($request->selected_sales_id == 0 && $request->selected_level == 0))
        {
            logging([], '잘못된 접근');
            return null;
        }
        else
        {
            $query = $this->merchandises->where('brand_id', $request->user()->brand_id);
            if(count($request->selected_idxs))
                $query = $query->whereIn('id', $request->selected_idxs);
            if($request->selected_sales_id && $request->selected_level)
            {
                $idx    = globalLevelByIndex($request->selected_level);
                $query  = $query->where('sales'.$idx.'_id', $request->selected_sales_id);
            }
            return $query;    
        }
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
        $bf_sales_key = $this->getSalesKeys($request);
        $aft_trx_fee = $request->sales_fee/100;
        $aft_sales_id = $request->sales_id;

        $mchts = $this->merchandiseBatch($request)->get();
        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            $bf_trx_fee  = $js_mcht[$bf_sales_key['sales_fee']];
            $bf_sales_id = $js_mcht[$bf_sales_key['sales_id']];            
            array_push($datas, [
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
            ]);
        }
        return $datas;
    }

    /**
     * 가맹점 수수료율 적용 포멧 목록
     */
    public function getMchtResource($request, $change_status)
    {
        $aft_trx_fee = $request->mcht_fee/100;
        $aft_hold_fee = $request->hold_fee/100;

        $mchts = $this->merchandiseBatch($request)->get();
        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            array_push($datas, [
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
            ]);
        }
        return $datas;
    }

    private function SfFeeApplyHistoryStore($brand_id, $sales_id, $sales_fee)
    {
        $u_apply_res = SfFeeApplyHistory::where('sales_id', $sales_id)
            ->where(['is_delete' => false])
            ->update(['is_delete' => true]);
        $c_apply_res = SfFeeApplyHistory::create([
            'brand_id' => $brand_id,
            'sales_id' => $sales_id,
            'trx_fee'  => $sales_fee,
        ]);
    }
    /**
     * 영업점 수수료율 즉시적용 
     */
    public function setSalesFeeDirect(Request $request)
    {
        $datas = $this->getSalesResource($request, true);
        $res = DB::transaction(function () use($datas, $request) {
            // merchandise change
            $sales_key = $this->getSalesKeys($request);
            $mchts = $this->merchandiseBatch($request)->update([
                $sales_key['sales_fee'] => $request->sales_fee/100,
                $sales_key['sales_id']  => $request->sales_id,
            ]);
            // sales fee change histories
            $this->SfFeeApplyHistoryStore($request->user()->brand_id, $request->sales_id, $request->sales_fee/100);
            return $this->manyInsert(new SfFeeChangeHistory(), $datas);
        });
        return $this->response($res ? 1 : 990);
    }

    /**
     * 영업점 수수료율 예약적용 
     */
    public function setSalesFeeBooking(Request $request)
    {
        $datas = $this->getSalesResource($request, false);
        $res = DB::transaction(function () use($datas, $request) {
            // sales fee change histories
            $this->SfFeeApplyHistoryStore($request->user()->brand_id, $request->sales_id, $request->sales_fee/100);
            return $this->manyInsert(new SfFeeChangeHistory(), $datas);
        });
        return $this->response($res ? 1 : 990);
    }


    /**
     * 가맹점 수수료율 즉시적용 
     */
    public function setMchtFeeDirect(Request $request)
    {
        $datas = $this->getMchtResource($request, true);
        $res = DB::transaction(function () use($datas, $request) {
            $mchts = $this->merchandiseBatch($request)->update([
                'trx_fee' => $request->mcht_fee/100,
                'hold_fee' => $request->hold_fee/100,
            ]);
            return $this->manyInsert(new MchtFeeChangeHistory(), $datas);
        });
        return $this->response($res ? 1 : 990);
    }

    /**
     * 가맹점 수수료율 예약적용 
     */
    public function setMchtFeeBooking(Request $request)
    {
        $datas = $this->getMchtResource($request, false);
        $res = $this->manyInsert(new MchtFeeChangeHistory(), $datas);
        return $this->response($res ? 1 : 990);
    }

    public function setEnabled(Request $request)
    {
        $cols = ['enabled' => $request->enabled];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->response(1);
    }
    /**
     * 커스텀 필터 적용 
     */
    public function setCustomFilter(Request $request)
    {
        $cols = ['custom_id' => $request->custom_id];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 사업자등록번호 적용
     */
    public function setBusinessNum(Request $request)
    {
        $cols = ['business_num' => $request->business_num];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->response(1);
    }
    
    /**
     * 계좌정보 적용 
    */
    public function setAccountInfo(Request $request)
    {
        $cols = [
            'acct_num' => $request->acct_num,
            'acct_name' => $request->acct_name,
            'acct_bank_code' => $request->acct_bank_code,
            'acct_bank_name' => $request->acct_bank_name,
        ];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->response(1);
    }
    /**
     * 노티 URL 일괄등록
     *
     * 가맹점 이상 가능
     *
     */
    public function setNotiUrl(Request $request)
    {
        $validated = $request->validate(['send_url'=>'required']);
        
        $notis = new NotiUrl();        
        $mcht_ids = $this->merchandiseBatch($request)->get(['id'])->pluck('id');
        $registered_notis = $notis
            ->whereIn('mcht_id', $mcht_ids)
            ->where('send_url', $request->send_url)
            ->get(['mcht_id']);

        $datas = [];
        foreach($mcht_ids as $mcht_id)
        {
            $registered = $registered_notis->first(function($noti) use ($mcht_id) {
                return $noti->mcht_id == $mcht_id;
            });
            if($registered)
            {
            }
            else
            {
                array_push($datas, [
                    'brand_id' => $request->user()->brand_id,
                    'mcht_id' => $mcht_id,
                    'send_url' => $request->send_url,
                    'noti_status' => $request->noti_status,
                    'note' => $request->note,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $res = $this->manyInsert($notis, $datas);
        $u_res = $this->merchandises->whereIn('id', $mcht_ids)->update(['use_noti' => true]);

        return $this->response(1, ['registered_notis'=>$registered_notis->pluck('id'), 'count'=>count($datas)]);
    }

}
