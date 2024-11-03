<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Models\Merchandise;
use App\Models\Merchandise\NotiUrl;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\MerchandiseColumnApplyBook;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthOperatorIP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Merchandise-Batch-Updater API
 *
 * 가맹점 일괄 업데이트 group 입니다.
 */
class BatchUpdateMchtController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $merchandises;

    public function __construct(Merchandise $merchandises)
    {
        $this->merchandises = $merchandises;
    }

    private function applyBook($query, $request, $cols)
    {
        $datas = $this->getApplyBookDatas($request, $query->pluck('id')->all(), 'mcht_id', $cols);
        $res = $this->manyInsert(new MerchandiseColumnApplyBook, $datas);
        return count($datas);
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->merchandiseBatch($request);
        if($request->apply_type === 0) 
            $row = $query->update($cols);
        else
            $row = $this->applyBook($query, $request, $cols);
        return $row;
    }

    /**
     * 선택된 가맹점 가져오기
     */
    private function merchandiseBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        if($apply_mode === 3)
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
            if($request->total_selected_count !== count($mcht_ids))
            {
                print_r(json_encode(['code'=>1999, 'message'=>'변경할 개수와 조회 개수가 같지 않습니다.', 'data'=>[]], JSON_UNESCAPED_UNICODE));
                exit;
            }
            return $this->merchandises->whereIn('id', $mcht_ids);
        }
        else if($apply_mode === 1)
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
        else
            return null;
    }

    /**
     * 가맹점/영업점 수수료율 즉시/예약적용 
     */
    public function feeApply(Request $request, $user)
    {
        $cond_1 = (Ablilty::isOperator($request) && AuthOperatorIP::valiate($request->user()->brand_id, $request->ip())) || Ablilty::isDevLogin($request);
        $cond_2 = Ablilty::isSalesforce($request);

        if($cond_1 || $cond_2)
        {
            $query = $this->merchandiseBatch($request);
            $row = MerchandiseFeeUpdater::apply($request, $user, $request->apply_type, $query);

            // 상위영업점 변경건일 시 히스토리 추가
            if($row && $user === 'salesforces')
                    MerchandiseFeeUpdater::SalesFeeHistoryUpdate($request);
            return $this->batchResponse($row, '가맹점');
        }
        else
            return $this->response(951);
    }

    public function setEnabled(Request $request)
    {
        $cols = ['enabled' => $request->enabled];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }
    
    /**
     * 커스텀 필터 적용 
     */
    public function setCustomFilter(Request $request)
    {
        $cols = ['custom_id' => $request->custom_id];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    /**
     * 사업자등록번호 적용
     */
    public function setBusinessNum(Request $request)
    {
        $cols = ['business_num' => $request->business_num];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }
    
    /**
     * 주민등록번호 적용
     */
    public function setResidentNum(Request $request)
    {
        $cols = ['resident_num' => $request->resident_num];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    /**
     * 수수료율 노출여부 적용
     */
    public function setShowFee(Request $request)
    {
        $cols = ['is_show_fee' => $request->is_show_fee];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
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
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
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
                    'pmod_id' => -1,
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
    
    /**
     * 휴대폰 최대인증 허용회수 
    */
    public function setPhoneAuthLimitCount(Request $request)
    {
        $cols = [
            'phone_auth_limit_count' => $request->phone_auth_limit_count,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    /**
     * 휴대폰 최대인증 허용회수 적용시간 
    */
    public function setPhoneAuthLimitTime(Request $request)
    {
        $cols = [
            'phone_auth_limit_s_tm' => $request->phone_auth_limit_s_tm,
            'phone_auth_limit_e_tm' => $request->phone_auth_limit_e_tm,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    /**
     * 지정시간 단걸결제금액한도 하향금액 
    */
    public function setSpecifiedTimeDisableLimit(Request $request)
    {
        $cols = [
            'specified_time_disable_limit' => $request->specified_time_disable_limit,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    /**
     * 지정시간 단걸결제금액한도 하향 적용시간 
    */
    public function setSpecifiedTimeDisableTime(Request $request)
    {
        $cols = [
            'single_payment_limit_s_tm' => $request->single_payment_limit_s_tm,
            'single_payment_limit_e_tm' => $request->single_payment_limit_e_tm,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    /**
     * 노티사용 여부 
    */
    public function setUseNoti(Request $request)
    {
        $cols = [
            'use_noti' => $request->use_noti,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }
}
