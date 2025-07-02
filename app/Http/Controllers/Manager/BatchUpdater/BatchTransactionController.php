<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use Illuminate\Http\Request;

/**
 * @group Withdraw-Batch-Book-Updater API
 *
 * 정산하기 group 입니다.
 */
class BatchTransactionController extends BatchUpdateController
{
    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function register(Request $request)
    {

    }
}
