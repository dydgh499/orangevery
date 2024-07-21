<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Models\Merchandise\PaymentModule;
use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Log\SfFeeApplyHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group PayModule-Batch-Updater API
 *
 * 결제모듈 일괄 업데이트 group 입니다.
 */
class BatchUpdatePayModuleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $pay_modules;

    public function __construct(PaymentModule $pay_modules)
    {
        $this->pay_modules = $pay_modules;
    }

    /**
     * 결제모듈 가져오기
     */
    private function payModuleBatch($request)
    {
        if(count($request->selected_idxs) == 0 && ($request->selected_sales_id == 0 && $request->selected_level == 0))
        {
            logging([], '잘못된 접근');
            return null;
        }
        else
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
    }
    
    /**
     * PG사 및 구간 적용
     */
    public function setPaymentGateway(Request $request)
    {
        $cols = ['pg_id' => $request->pg_id, 'ps_id' => $request->ps_id];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 이상거래 한도 적용 
     */
    public function setAbnormalTransLimit(Request $request)
    {
        $cols = ['abnormal_trans_limit' => $request->abnormal_trans_limit];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 동일카드 결제허용 회수 적용 
     */
    public function setDupPayCountValidation(Request $request)
    {
        $cols = ['pay_dupe_limit' => $request->pay_dupe_limit];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 중복결제 하한금 적용 
     */
    public function setDupPayLeastValidation(Request $request)
    {
        $cols = ['pay_dupe_least' => $request->pay_dupe_least];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
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
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
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
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제창 노출여부 적용 
     */
    public function setShowPayView(Request $request)
    {
        $cols = ['show_pay_view' => $request->show_pay_view];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * MID 일괄적용
     *
     */
    public function setMid(Request $request)
    {
        $cols = ['mid' => $request->mid];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * TID 일괄적용
     *
     */
    public function setTid(Request $request)
    {
        $cols = ['tid' => $request->tid];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * API KEY 일괄적용
     *
     */
    public function setApiKey(Request $request)
    {
        $cols = ['api_key' => $request->api_key];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * SUB KEY 일괄적용
     *
     */
    public function setSubKey(Request $request)
    {
        $cols = ['sub_key' => $request->sub_key];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    public function setInstallment(Request $request)
    {
        $cols = ['installment' => $request->installment];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제모듈 별칭 일괄적용
     *
     */
    public function setNote(Request $request)
    {
        $cols = ['note' => $request->note];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제모듈 실시간 사용여부 일괄적용
     *
     */
    public function setUseRealtimeDeposit(Request $request)
    {
        $cols = ['use_realtime_deposit' => $request->use_realtime_deposit];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제모듈 허용간격 일괄적용
     * 
     */
    public function setPaymentTermMin(Request $request)
    {
        $cols = ['payment_term_min' => $request->payment_term_min];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $res = $this->payModuleBatch($request)->update(['is_delete' => true]);
        return $this->response($res ? 1 : 990);
    }
}
