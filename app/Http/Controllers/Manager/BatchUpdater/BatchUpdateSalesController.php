<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\SalesforceController;

use App\Models\Salesforce\SalesforceColumnApplyBook;
use App\Models\Salesforce;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Salesforce-Batch-Updater API
 *
 * 영업점 일괄 업데이트 group 입니다.
 */
class BatchUpdateSalesController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $salesforces;

    public function __construct(Salesforce $salesforces)
    {
        $this->salesforces = $salesforces;
    }

    private function applyBook($query, $request, $cols)
    {
        $datas = $this->getApplyBookDatas($request, $query->pluck('id')->all(), 'sales_id', $cols);
        $res = $this->manyInsert(new SalesforceColumnApplyBook, $datas);
        return count($datas);
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->salesforceBatch($request);
        if($request->apply_type === 0) 
            $row = $query->update($cols);
        else
            $row = $this->applyBook($query, $request, $cols);
        return $row;
    }

    /**
     * 선택된 영업점 가져오기
     */
    private function salesforceBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        if($apply_mode === 3)
        {
            $filter_request = new Request();
            $filter_request->merge($request->filter);
            $filter_request->setUserResolver(function () use ($request) {
                return $request->user();
            });
            $query = resolve(SalesforceController::class)->commonSelect($filter_request);

            if($request->total_selected_count !== (clone $query)->count())
            {
                print_r(json_encode(['code'=>1999, 'message'=>'변경할 개수와 조회 개수가 같지 않습니다.', 'data'=>[]], JSON_UNESCAPED_UNICODE));
                exit;
            }
            return $query;
        }
        else if($apply_mode === 1)
        {
            return $this->salesforces
                ->where('brand_id', $request->user()->brand_id)
                ->whereIn('id', $request->selected_idxs);
        }
        else
            return null;
    }

    public function setSettleTaxType(Request $request)	
    {
        $cols = ['settle_tax_type' => $request->settle_tax_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');

    }
    
    public function setSettleCycle(Request $request)	
    {
        $cols = ['settle_cycle' => $request->settle_cycle];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');
    }
    
    public function setSettleDay(Request $request)	
    {
        $cols = ['settle_day' => $request->settle_day];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');
    }
    
    public function setIsAbleModifyMcht(Request $request)	
    {
        $cols = ['is_able_modify_mcht' => $request->is_able_modify_mcht];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');
    }
    
    public function setViewType(Request $request)	
    {
        $cols = ['view_type' => $request->view_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');
    }
    
    public function setAccountInfo(Request $request)	
    {
        $cols = [
            'acct_num' => $request->acct_num,
            'acct_name' => $request->acct_name,
            'acct_bank_code' => $request->acct_bank_code,
            'acct_bank_name' => $request->acct_bank_name,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');
    }
    
    public function setNote(Request $request)	
    {
        $cols = ['note' => $request->note];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업점');
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
