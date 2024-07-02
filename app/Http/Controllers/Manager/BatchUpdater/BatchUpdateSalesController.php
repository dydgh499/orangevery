<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

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

    /**
     * 선택된 영업점 가져오기
     */
    private function salesforceBatch($request)
    {
        return $this->salesforces->where('brand_id', $request->user()->brand_id)
            ->whereIn('id', $request->selected_idxs);
    }

    public function setSettleTaxType(Request $request)	
    {
        $cols = ['settle_tax_type' => $request->settle_tax_type];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->response(1);

    }
    
    public function setSettleCycle(Request $request)	
    {
        $cols = ['settle_cycle' => $request->settle_cycle];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->response(1);
    }
    
    public function setSettleDay(Request $request)	
    {
        $cols = ['settle_day' => $request->settle_day];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->response(1);
    }
    
    public function setIsAbleModifyMcht(Request $request)	
    {
        $cols = ['is_able_modify_mcht' => $request->is_able_modify_mcht];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->response(1);
    }
    
    public function setViewType(Request $request)	
    {
        $cols = ['view_type' => $request->view_type];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->response(1);
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
        return $this->response(1);
    }
    
    public function setNote(Request $request)	
    {
        $cols = ['note' => $request->note];
        $row = $this->salesforceBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $res = $this->salesforceBatch($request)->update(['is_delete' => true]);
        return $this->response($res ? 1 : 990);
    }
}
