<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Models\Merchandise;
use App\Models\Merchandise\NotiUrl;
use App\Models\Merchandise\PaymentModule;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Log\SfFeeApplyHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthOperatorIP;

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

    private function batchResponse($row)
    {
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 업데이트되었습니다.' : '업데이트된 가맹점이 존재하지 않습니다.');
    }
    /**
     * 선택된 가맹점 가져오기
     */
    private function merchandiseBatch($request)
    {
        $cond_1 = count($request->selected_idxs);
        $cond_2 = ($request->selected_sales_id && $request->selected_level);
        $cond_3 = ($request->selected_all && $request->filter['page_size']);  //전체 변경
        
        if($cond_1 || $cond_2 || $cond_3)
        {
            if($cond_3)
            {
                $filter_request = new Request();
                $filter_request->merge($request->filter);
                $filter_request->setUserResolver(function () use ($request) {
                    return $request->user();
                });
                
                $inst = resolve(MerchandiseController::class);
                if($inst->isByPayModule($filter_request))
                    $query = $inst->byPayModules($filter_request, false);
                else 
                    $query = $inst->byNormalIndex($filter_request, false);

                $mcht_ids = $query->pluck('merchandises.id')->all();
                return $this->merchandises->whereIn('id', $mcht_ids);
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
        else
        {
            logging([], '잘못된 접근');
            echo "wrong access";
            return null;
        }
    }

    /**
     * 가맹점/영업점 수수료율 즉시/예약적용 
     */
    public function feeApply(Request $request, $user, $type)
    {
        $cond_1 = (Ablilty::isOperator($request) && AuthOperatorIP::valiate($request->user()->brand_id, $request->ip())) || Ablilty::isDevLogin($request);
        $cond_2 = Ablilty::isSalesforce($request);

        if($cond_1 || $cond_2)
        {
            $query = $this->merchandiseBatch($request);
            $row = MerchandiseFeeUpdater::apply($request, $user, $type, $query);

            // 상위영업점 변경건일 시 히스토리 추가
            if($row && $user === 'salesforces')
                    MerchandiseFeeUpdater::SalesFeeHistoryUpdate($request);
            return $this->batchResponse($row);
        }
        else
            return $this->response(951);
    }

    public function setEnabled(Request $request)
    {
        $cols = ['enabled' => $request->enabled];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    /**
     * 커스텀 필터 적용 
     */
    public function setCustomFilter(Request $request)
    {
        $cols = ['custom_id' => $request->custom_id];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 사업자등록번호 적용
     */
    public function setBusinessNum(Request $request)
    {
        $cols = ['business_num' => $request->business_num];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    
    /**
     * 수수료율 노출여부 적용
     */
    public function setShowFee(Request $request)
    {
        $cols = ['is_show_fee' => $request->is_show_fee];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->batchResponse($row);
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
        return $this->batchResponse($row);
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

        $mcht_ids = $this->merchandiseBatch($request)->pluck('id')->all();
        $registered_notis = NotiUrl::whereIn('mcht_id', $mcht_ids)
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
                $data[] = [
                    'brand_id' => $request->user()->brand_id,
                    'mcht_id' => $mcht_id,
                    'send_url' => $request->send_url,
                    'noti_status' => $request->noti_status,
                    'note' => $request->note,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        $res = $this->manyInsert(new NotiUrl, $datas);
        $row = $this->merchandises->whereIn('id', $mcht_ids)->update(['use_noti' => true]);

        $message = count($datas).'개의 노티가 추가되었습니다.';
        $res_data = ['registered_notis'=>$registered_notis->pluck('id'), 'count'=>count($datas)];
        if($registered_notis && $registered_notis->count())
        {
            $message .= '<br>'.($registered_notis->count()).'개의 가맹점은 이미 같은 주소로 등록되어 추가되지 않았습니다.';
        }
        return $this->extendResponse(1, $message, $res_data);
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $row = DB::transaction(function () use($request) {
            $query = $this->merchandiseBatch($request);
            $mcht_ids = (clone $query)->pluck('id')->all();

            $row = (clone $query)->update(['is_delete' => true]);
            $res = PaymentModule::whereIn('id', $mcht_ids)->update(['is_delete' => true]);
            $res = NotiUrl::whereIn('id', $mcht_ids)->update(['is_delete' => true]);
            return $row;
        });
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 가맹점이 존재하지 않습니다.');        
    }
}
