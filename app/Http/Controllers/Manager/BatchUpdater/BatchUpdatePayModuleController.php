<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\Merchandise\PaymentModuleController;
use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;

use App\Models\Merchandise\PaymentModule;

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

    private function batchResponse($row)
    {
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 업데이트되었습니다.' : '업데이트된 결제모듈이 존재하지 않습니다.');
    }
    /**
     * 결제모듈 가져오기
     */
    private function payModuleBatch($request)
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
                $query = resolve(PaymentModuleController::class)->commonSelect($filter_request);
                if($request->total_selected_count !== (clone $query)->count())
                {
                    print_r(json_encode(['code'=>1999, 'message'=>'변경할 개수와 조회 개수가 같지 않습니다.', 'data'=>[]], JSON_UNESCAPED_UNICODE));
                    exit;
                }
                return $query;

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
        else
        {
            logging([], '잘못된 접근');
            echo "wrong access";
            return null;
        }
    }
    
    /**
     * PG사 및 구간 적용
     */
    public function setPaymentGateway(Request $request)
    {
        $cols = ['pg_id' => $request->pg_id, 'ps_id' => $request->ps_id];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 이상거래 한도 적용 
     */
    public function setAbnormalTransLimit(Request $request)
    {
        $cols = ['abnormal_trans_limit' => $request->abnormal_trans_limit];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 동일카드 결제허용 회수 적용 
     */
    public function setDupPayCountValidation(Request $request)
    {
        $cols = ['pay_dupe_limit' => $request->pay_dupe_limit];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 중복결제 하한금 적용 
     */
    public function setDupPayLeastValidation(Request $request)
    {
        $cols = ['pay_dupe_least' => $request->pay_dupe_least];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
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
        return $this->batchResponse($row);
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
        return $this->batchResponse($row);
    }

    /**
     * 결제창 보안등급 적용 
     */
    public function setShowPayView(Request $request)
    {
        $cols = ['pay_window_secure_level' => $request->pay_window_secure_level];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * MID 일괄적용
     *
     */
    public function setMid(Request $request)
    {
        $cols = ['mid' => $request->mid];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * TID 일괄적용
     *
     */
    public function setTid(Request $request)
    {
        $cols = ['tid' => $request->tid];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * API KEY 일괄적용
     *
     */
    public function setApiKey(Request $request)
    {
        $cols = ['api_key' => $request->api_key];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * SUB KEY 일괄적용
     *
     */
    public function setSubKey(Request $request)
    {
        $cols = ['sub_key' => $request->sub_key];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    public function setInstallment(Request $request)
    {
        $cols = ['installment' => $request->installment];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 결제모듈 별칭 일괄적용
     *
     */
    public function setNote(Request $request)
    {
        $cols = ['payment_modules.note' => $request->note];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 결제모듈 실시간 사용여부 일괄적용
     *
     */
    public function setUseRealtimeDeposit(Request $request)
    {
        $cols = ['use_realtime_deposit' => $request->use_realtime_deposit];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 결제모듈 이체 모듈 타입 일괄적용
     *
     */
    public function setFinId(Request $request)
    {
        $cols = ['fin_id' => $request->fin_id];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    

    /**
     * 결제모듈 허용간격 일괄적용
     * 
     */
    public function setPaymentTermMin(Request $request)
    {
        $cols = ['payment_term_min' => $request->payment_term_min];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->batchResponse($row);
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
        $row = $this->payModuleBatch($request)->update(['pay_window_secure_level' => $request->pay_window_secure_level]);
        return $this->batchResponse($row);
    }

    public function setPayWindowExtendHour(Request $request)
    {
        $row = $this->payModuleBatch($request)->update(['pay_window_extend_hour' => $request->pay_window_extend_hour]);
        return $this->batchResponse($row);
    }
}
