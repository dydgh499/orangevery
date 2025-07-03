<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Requests\Manager\BulkRegister\BulkTransactionRequest;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;
use App\Models\Pay\Transaction;

/**
 * @group Withdraw-Batch-Book-Updater API
 *
 * 정산하기 group 입니다.
 */
class BatchTransactionController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $transactions;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target       = '거래건';
    }
    
    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function register(BulkTransactionRequest $request)
    {
        /** TODO:
         * 1. 계좌 조회
         * 2. 거래건 등록
         * 3. 결제 진행
         * 4. 결제 업데이트
         * 5. 성공건 수집
         * 6. 이체 진행
         * 7. 이체 업데이트
         */
        
    }
}
