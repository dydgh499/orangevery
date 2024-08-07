<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\SalesforceController;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Salesforce-Batch-Updater API
 *
 * 가맹점 일괄 업데이트 group 입니다.
 */
class BatchUpdateSalesController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $salesforces;

    public function __construct(Salesforce $salesforces)
    {
        $this->salesforces = $salesforces;
    }

    private function batchResponse($row)
    {
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 업데이트되었습니다.' : '업데이트된 가맹점이 존재하지 않습니다.');
    }

    /**
     * 선택된 영업점 가져오기
     */
    private function salesforceBatch($request)
    {
        $cond_1 = count($request->selected_idxs);
        $cond_3 = ($request->selected_all && $request->filter['page_size']);  //전체 변경

        if($cond_1 || $cond_3)
        {
            if($cond_3)
            {
                $filter_request = new Request();
                $filter_request->merge($request->filter);
                $filter_request->setUserResolver(function () use ($request) {
                    return $request->user();
                });
                return resolve(SalesforceController::class)->commonSelect($filter_request);
            }
            else
                return $this->salesforces->where('brand_id', $request->user()->brand_id)->whereIn('id', $request->selected_idxs);
        }
    }

    public function setSettleTaxType(Request $request)	
    {
        $cols = ['settle_tax_type' => $request->settle_tax_type];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);

    }
    
    public function setSettleCycle(Request $request)	
    {
        $cols = ['settle_cycle' => $request->settle_cycle];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    
    public function setSettleDay(Request $request)	
    {
        $cols = ['settle_day' => $request->settle_day];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    
    public function setIsAbleModifyMcht(Request $request)	
    {
        $cols = ['is_able_modify_mcht' => $request->is_able_modify_mcht];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    
    public function setViewType(Request $request)	
    {
        $cols = ['view_type' => $request->view_type];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    
    public function setAccountInfo(Request $request)	
    {
        $cols = [
            'acct_num' => $request->acct_num,
            'acct_name' => $request->acct_name,
            'acct_bank_code' => $request->acct_bank_code,
            'acct_bank_name' => $request->acct_bank_name,
        ];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);
    }
    
    public function setNote(Request $request)	
    {
        $cols = ['note' => $request->note];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->batchResponse($row);
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $row = $this->salesforceBatch($request)->update(['is_delete' => true]);
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 영업점이 존재하지 않습니다.');
    }
}
