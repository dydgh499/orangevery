<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Models\Transaction;

use App\Http\Controllers\Utils\Comm;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Models\Brand;
use App\Http\Controllers\Manager\Service\BrandInfo;
use App\Http\Controllers\Manager\Transaction\SettleAmountCalculator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @group Transaction-Batch-Updater API
 *
 * 거래정보 일괄 업데이트 group 입니다.
 */
class BatchUpdateTransactionController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->base_noti_url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->transactionBatch($request);
        if($request->apply_type === 0) 
            $row = $query->update($cols);
        else
            $this->wrongTypeAccess();
        return $row;
    }

    /**
     * 선택된 영업점 가져오기
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

    public function singleDepositCancelJobReservation(Request $request)
    {
        if($request->user()->tokenCan(35))
        {
            $validated = $request->validate(['selected_idxs.*'=>'required|numeric']);
            $url = $this->base_noti_url.'/single-deposit-cancel-job-reservation';
            $res = Comm::post($url, [
                'trx_ids' => $request->selected_idxs,
            ]);
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        }
        else
            return $this->response(951);
    }

    public function removeDepositFee(Request $request)
    {
        $this->transactions
            ->whereIn('id', $request->selected_idxs)
            ->update(['mcht_settle_fee' => 0]);

        $db_trans = $this->transactions
            ->whereIn('id', $request->selected_idxs)
            ->get();

        $brand = Brand::where('id', $request->user()->brand_id)->first();
        $trans = json_decode(json_encode($db_trans), true);
        $trans = SettleAmountCalculator::setSettleAmount($trans, $brand->dev_settle_type);
        $i=0;

        foreach($db_trans as $tran)
        {
            $tran->mcht_settle_amount = $trans[$i]['mcht_settle_amount'];
            $tran->save();
            $i++;
        }
        return $this->response(0);
    }

    /*
     * 정산일 변경
     */
    public function changeSettleDate(Request $request)
    {
        $settle_dt = Carbon::createFromFormat('Y-m-d', (string)$request->settle_dt)->format('Ymd');
        $row = $this->getApplyRow($request, ['settle_dt' => $settle_dt]);
        return $this->batchResponse($row, '매출');
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
        $row = $this->transactionBatch($request)->delete();
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 매출이 존재하지 않습니다.');
    }

    public function salesFeeApply(Request $request)
    {
        $db_trans = $this->transactionBatch($request)->get();
        $idx  = globalLevelByIndex($request->level);

        $sales_id_field = "sales{$idx}_id";
        $sales_fee_field = "sales{$idx}_fee";
        foreach($db_trans as $tran)
        {
            $tran->{$sales_id_field} = $request->sales_id;
            $tran->{$sales_fee_field} = $request->sales_fee / 100;
        }
        
        $brand = Brand::where('id', $request->user()->brand_id)->first();
        $trans = SettleAmountCalculator::setSettleAmount(
            json_decode(json_encode($db_trans), true), 
            $brand->dev_settle_type
        );

        foreach($db_trans as $key => $tran)
        {
            $fields = [
                'brand_settle_amount',
                'dev_realtime_settle_amount',
                'dev_settle_amount',
                'sales5_settle_amount',
                'sales4_settle_amount',
                'sales3_settle_amount',
                'sales2_settle_amount',
                'sales1_settle_amount',
                'sales0_settle_amount',
                'mcht_settle_amount',
            ];

            foreach ($fields as $field) 
            {
                $tran->{$field} = $trans[$key][$field];
            }
            $tran->save();
        }
        return $this->batchResponse(count($db_trans), '매출');
    }
}
