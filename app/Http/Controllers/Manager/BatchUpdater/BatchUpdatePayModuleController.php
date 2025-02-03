<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;

use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\PaymentModuleColumnApplyBook;
use App\Http\Requests\Manager\BulkRegister\BulkPayModuleRequest;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use Illuminate\Http\Request;

/**
 * @group PayModule-Batch-Updater API
 *
 * 결제모듈 일괄 업데이트 group 입니다.
 */
class BatchUpdatePayModuleController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $pay_modules;

    public function __construct(PaymentModule $pay_modules)
    {
        $this->pay_modules = $pay_modules;
    }

    private function applyBook($query, $request, $cols)
    {
        $datas = $this->getApplyBookDatas($request, $query->pluck('payment_modules.id')->all(), 'pmod_id', $cols);
        $res = $this->manyInsert(new PaymentModuleColumnApplyBook, $datas);
        return count($datas);
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->payModuleBatch($request);
        if($request->apply_type === 0) 
            $row = $query->update($cols);
        else
            $row = $this->applyBook($query, $request, $cols);
        return $row;
    }

    /**
     * 결제모듈 가져오기
     */
    private function payModuleBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        if($apply_mode === 3)
        {
            $filter_request = new Request();
            $filter_request->merge($request->filter);
            $filter_request->setUserResolver(function () use ($request) {
                return $request->user();
            });
            $query = resolve(PaymentModuleController::class)->commonSelect($filter_request);
            if($request->total_selected_count !== (clone $query)->count())
            {
                print_r(json_encode(['code'=>1999, 'message'=>'변경할 개수와 조회 개수가 같지 않습니다.', 'data'=>[]], JSON_UNESCAPED_UNICODE));
                exit;
            }
            return $query;
        }
        else if($apply_mode === 1)
        {
            $query = $this->pay_modules->where('payment_modules.brand_id', $request->user()->brand_id);
            if(count($request->selected_idxs))
                $query = $query->whereIn('id', $request->selected_idxs);
            if($request->selected_sales_id && $request->selected_level)
            {
                $idx    = globalLevelByIndex($request->selected_level);
                $query  = $query
                    ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
                    ->where('merchandises.sales'.$idx.'_id', $request->selected_sales_id);
            }
            return $query;
        }
        else
            return null;
    }
    
    /**
     * PG사 및 구간 적용
     */
    public function setPaymentGateway(Request $request)
    {
        $cols = ['pg_id' => $request->pg_id, 'ps_id' => $request->ps_id];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 이상거래 한도 적용 
     */
    public function setAbnormalTransLimit(Request $request)
    {
        $cols = ['abnormal_trans_limit' => $request->abnormal_trans_limit];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 동일카드 결제허용 회수 적용 
     */
    public function setDupPayCountValidation(Request $request)
    {
        $cols = ['pay_dupe_limit' => $request->pay_dupe_limit];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 중복결제 하한금 적용 
     */
    public function setDupPayLeastValidation(Request $request)
    {
        $cols = ['pay_dupe_least' => $request->pay_dupe_least];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 결제한도 적용 
     */
    public function setPayLimit(Request $request)
    {
        $type = 'pay_'.$request->type.'_limit';
        if($request->type == 'day')
            $limit = $request->pay_day_limit;
        else if($request->type == 'month')
            $limit = $request->pay_month_limit;
        else if($request->type == 'year')
            $limit = $request->pay_year_limit;
        else if($request->type == 'single')
            $limit = $request->pay_single_limit;
        
        $cols = [$type => $limit];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 결제금지시간 적용 
     */
    public function setForbiddenPayTime(Request $request)
    {
        $cols = [
            'pay_disable_s_tm' => $request->pay_disable_s_tm,
            'pay_disable_e_tm' => $request->pay_disable_e_tm,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 정산일 일괄적용
     */
    public function setSettleType(Request $request)
    {
        $cols = ['settle_type' => $request->settle_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 이체수수료 일괄적용
     */
    public function setSettleFee(Request $request)
    {
        $cols = ['settle_fee' => $request->settle_fee];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * MID 일괄적용
     *
     */
    public function setMid(Request $request)
    {
        $cols = ['mid' => $request->mid];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * TID 일괄적용
     *
     */
    public function setTid(Request $request)
    {
        $cols = ['tid' => $request->tid];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * PMID 일괄적용
     *
     */
    public function setPmid(Request $request)
    {
        $cols = ['p_mid' => $request->p_mid];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * API KEY 일괄적용
     *
     */
    public function setApiKey(Request $request)
    {
        $cols = ['api_key' => $request->api_key];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * SUB KEY 일괄적용
     *
     */
    public function setSubKey(Request $request)
    {
        $cols = ['sub_key' => $request->sub_key];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    public function setInstallment(Request $request)
    {
        $cols = ['installment' => $request->installment];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 결제모듈 별칭 일괄적용
     *
     */
    public function setNote(Request $request)
    {
        $cols = ['payment_modules.note' => $request->note];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 결제모듈 실시간 사용여부 일괄적용
     *
     */
    public function setUseRealtimeDeposit(Request $request)
    {
        $cols = ['use_realtime_deposit' => $request->use_realtime_deposit];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 결제모듈 이체 모듈 타입 일괄적용
     *
     */
    public function setFinId(Request $request)
    {
        $cols = ['fin_id' => $request->fin_id];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }
    
    /**
     * 결제모듈 이체 딜레이 일괄적용
     *
     */
    public function setFinTrxDelay(Request $request)
    {
        $cols = ['fin_trx_delay' => $request->fin_trx_delay];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');        
    }

    /**
     * 결제모듈 허용간격 일괄적용
     * 
     */
    public function setPaymentTermMin(Request $request)
    {
        $cols = ['payment_term_min' => $request->payment_term_min];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $row = $this->payModuleBatch($request)->update(['payment_modules.is_delete' => true]);
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 결제모듈이 존재하지 않습니다.');
    }

    public function setPayWindowSecureLevel(Request $request)
    {
        $cols = ['pay_window_secure_level' => $request->pay_window_secure_level];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    public function setPayWindowExtendHour(Request $request)
    {
        $cols = ['pay_window_extend_hour' => $request->pay_window_extend_hour];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }
    
    public function setCxlType(Request $request)
    {
        $cols = ['cxl_type' => $request->cxl_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    public function setPayLimitType(Request $request)
    {
        $cols = ['pay_limit_type' => $request->pay_limit_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '결제모듈');
    }

    
    /**
     * 결제모듈 대량등록
     *
     * 운영자 이상 가능
     */
    public function register(BulkPayModuleRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        if(count($datas) > 1000)
            return $this->extendResponse(1000, '결제모듈은 한번에 최대 1000개까지 등록할 수 있습니다.');
        else
        {
            $pay_modules = $datas->map(function ($data) use($current, $brand_id) {
                $data['brand_id'] = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                return $data;
            })->toArray();

            $res = $this->manyInsert($this->pay_modules, $pay_modules);
            return $this->response($res ? 1 : 990);
        }
    }
}
