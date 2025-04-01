<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\SalesforceController;

use App\Models\Salesforce\SalesforceColumnApplyBook;
use App\Models\Salesforce;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkSalesforceRequest;

use App\Http\Controllers\Auth\AuthPasswordChange;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Salesforce-Batch-Updater API
 *
 * 영업라인 일괄 업데이트 group 입니다.
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
     * 선택된 영업라인 가져오기
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
        return $this->batchResponse($row, '영업라인');

    }
    
    public function setSettleCycle(Request $request)	
    {
        $cols = ['settle_cycle' => $request->settle_cycle];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업라인');
    }
    
    public function setSettleDay(Request $request)	
    {
        $cols = ['settle_day' => $request->settle_day];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업라인');
    }
    
    public function setIsAbleModifyMcht(Request $request)	
    {
        $cols = ['auth_level' => $request->auth_level];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업라인');
    }
    
    public function setViewType(Request $request)	
    {
        $cols = ['view_type' => $request->view_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업라인');
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
        return $this->batchResponse($row, '영업라인');
    }

    public function setBusinessType(Request $request)
    {
        $cols = [
            'business_type' => $request->business_type,
        ];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '가맹점');
    }

    public function setNote(Request $request)	
    {
        $cols = ['note' => $request->note];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '영업라인');
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $row = $this->salesforceBatch($request)->update(['is_delete' => true]);
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 영업라인이 존재하지 않습니다.');
    }

    
    public function register(BulkSalesforceRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();

        $exist_names = $this->isExistBulkUserName($brand_id, $datas->pluck('user_name')->all());
        $exist_sales = $this->isExistBulkMutual($this->salesforces, $brand_id, 'sales_name', $datas->pluck('sales_name')->all());
        
        if(count($exist_names))
            return $this->extendResponse(1000, join(',', $exist_names).'는 이미 존재하는 아이디 입니다.');
        else if(count($exist_sales))
            return $this->extendResponse(1000, join(',', $exist_sales).'는 이미 존재하는 상호 입니다.');
        else
        {
            foreach($datas as $data)
            {
                [$result, $msg] = AuthPasswordChange::registerValidate($data['user_name'], $data['user_pw']);
                if($result === false)
                    return $this->extendResponse(954, $data['user_name']." ".$msg, []);
            }

            $salesforces = $datas->map(function ($data) use($current, $brand_id) {
                $data['user_pw'] = Hash::make($data['user_pw'].$current);
                $data['brand_id'] = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                return $data;
            })->toArray();

            $res = $this->manyInsert($this->salesforces, $salesforces);
            return $this->response($res ? 1 : 990);
        }
    }
}
