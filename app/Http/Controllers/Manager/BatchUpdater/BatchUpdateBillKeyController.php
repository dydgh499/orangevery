<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;

use App\Models\Merchandise\BillKey;
use App\Http\Controllers\Utils\Comm;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use Illuminate\Http\Request;

/**
 * @group BillKey-Batch-Updater API
 *
 * 빌키 일괄 업데이트 group 입니다.
 */
class BatchUpdateBillKeyController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $bill_keys;

    public function __construct(BillKey $bill_keys)
    {
        $this->bill_keys = $bill_keys;
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->billKeyBatch($request);
        if($request->apply_type === 0) 
            $row = $query->update($cols);
        else
            $this->wrongTypeAccess();
        return $row;
    }

    /**
     * 빌키 가져오기
     */
    private function billKeyBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        if($apply_mode === 3)
            $this->wrongTypeAccess();
        else if($apply_mode === 1)
            return $this->bill_keys->whereIn('bill_keys.id', $request->selected_idxs);
        else
            $this->wrongTypeAccess();
    }
    
    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $query = $this->billKeyBatch($request);
        $bill_keys = (clone $query)
            ->join('payment_modules', 'bill_keys.pmod_id', '=', 'payment_modules.id')
            ->get([
                'payment_modules.id', 'payment_modules.mid', 'payment_modules.pay_key',
                'bill_keys.bill_key', 'bill_keys.id as bill_id'
            ]);

        $success = [];
        $fails   = [];
        foreach($bill_keys as $bill_key)
        {
            $data = [
                'mid'       => $bill_key->mid,
                'ord_num'   => $bill_key->bill_id."BD".date("YmdHis"),
                'bill_key'  => $bill_key->bill_key,
            ];
            $res = Comm::destroy(env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/bill-key', $data, [
                'Authorization' => $bill_key->pay_key
            ]);
            $success[] = $bill_key->bill_id;
        }
        if(count($success))
            $this->bill_keys->whereIn('id', $success)->delete();
        if(count($fails))
        {
            $message = "일괄작업에 실패한 삭제건들이 존재합니다.\n\n".json_encode($fails, JSON_UNESCAPED_UNICODE);
            return $this->extendResponse(990, $message);
        }
        else
            return $this->extendResponse(1, count($success)."개가 삭제되었습니다.");
    }
}
