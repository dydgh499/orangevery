<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Enums\HistoryType;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;

use App\Models\Transaction;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use Illuminate\Http\Request;
/**
 * @group Transaction-Batch-Updater API
 *
 * 거래정보 일괄 업데이트 group 입니다.
 */
class BatchUpdateTransactionController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $transactions, $target;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target = '매출';
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->transactionBatch($request);
        if($request->apply_type === 0) 
            $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $cols, 'id');
        else
            $this->wrongTypeAccess();
        return $row;
    }

    /**
     * 선택된 영업라인 가져오기
     */
    private function transactionBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        if($apply_mode === 3)
            $this->wrongTypeAccess();
        else if($apply_mode === 1)
        {
            return $this->transactions
                ->where('brand_id', $request->user()->brand_id)
                ->whereIn('id', $request->selected_idxs);
        }
        else
            $this->wrongTypeAccess();
    }

    /**
     * 커스텀 필터 적용 
     */
    public function setCustomFilter(Request $request)
    {
        $row = $this->getApplyRow($request, ['custom_id' => $request->custom_id]);
        return $this->batchResponse($row, '매출');
    }

    /**
     * 장비 타입 적용 
     */
    public function setTerminalId(Request $request)
    {
        $row = $this->getApplyRow($request, ['terminal_id' => $request->terminal_id]);
        return $this->batchResponse($row, '매출');
    }

    /**
     * MID 적용 
     */
    public function setMid(Request $request)
    {
        $row = $this->getApplyRow($request, ['mid' => $request->mid]);
        return $this->batchResponse($row, '매출');
    }

    /**
     * MID 적용 
     */
    public function setTid(Request $request)
    {
        $row = $this->getApplyRow($request, ['tid' => $request->tid]);
        return $this->batchResponse($row, '매출');
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $query = $this->transactionBatch($request);
        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'id', '', HistoryType::DELETE, false);
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 매출이 존재하지 않습니다.');
    }
}
