<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\Withdraws\VirtualAccountController;

use App\Models\Withdraws\VirtualAccount;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkVirtualAccountRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group WAllet-Batch-Updater API
 *
 * 정산지갑 일괄 업데이트 group 입니다.
 */
class BatchUpdateWalletController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $virtual_accounts, $target;

    public function __construct(VirtualAccount $virtual_accounts)
    {
        $this->virtual_accounts = $virtual_accounts;
        $this->target = '정산지갑';
    }

    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function register(BulkVirtualAccountRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();

        $virtual_accounts = $datas->map(function ($data) use($request, $current, $brand_id) {
            $data['brand_id']       = $brand_id;
            $data['account_code'] = VirtualAccountController::generateCode();
            $data['level']      = 10;
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        
        $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->virtual_accounts, $virtual_accounts, 'account_name', $current, $brand_id);
        return $this->response(1, $ids);
    }
}
