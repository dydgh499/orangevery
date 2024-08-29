<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;

use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Merchandise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthOperatorIP;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;
use Carbon\Carbon;

/**
 * @group Fee-Change-History API
 *
 * 수수료율 변경이력 API 입니다.
 */
class FeeChangeHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $mcht_fee_histories;
    protected $sf_fee_histories;

    public function __invoke()
    {
        $mcht_histories = MchtFeeChangeHistory::where('is_delete', false)
                    ->where('change_status', 0)
                    ->where('apply_dt', Carbon::now()->format('Y-m-d'))
                    ->orderby('created_at', 'asc')
                    ->get();
        $sf_histories = SfFeeChangeHistory::where('is_delete', false)
                    ->where('change_status', 0)
                    ->where('apply_dt', Carbon::now()->format('Y-m-d'))
                    ->orderby('created_at', 'asc')
                    ->get();

        $date = date('Y-m-d H:i:s');
        foreach($sf_histories as $sf_history)
        {
            DB::transaction(function () use($sf_history, $date) {
                $sales_key = MerchandiseFeeUpdater::getSalesKeys($sf_history->level);
                Merchandise::where('id', $sf_history->mcht_id)
                    ->update([
                        $sales_key['sales_id'] => $sf_history->aft_sales_id,
                        $sales_key['sales_fee'] => $sf_history->aft_trx_fee/100,
                    ]);
                $sf_history->change_status = true;
                $sf_history->updated_at = $date;
                $sf_history->save();
            });
        }
        
        foreach($mcht_histories as $mcht_history)
        {
            DB::transaction(function () use($mcht_history, $date) {
                Merchandise::where('id', $mcht_history->mcht_id)
                    ->update([
                        'hold_fee'  => $mcht_history->aft_hold_fee/100,
                        'trx_fee' => $mcht_history->aft_trx_fee/100,
                    ]);
                $mcht_history->change_status = true;
                $mcht_history->updated_at = $date;
                $mcht_history->save();
            });

        }

        logging([
            'mcht_histories'=> count($mcht_histories),
            'sf_histories'  => count($sf_histories),
        ], 'fee-book-apply-scheduler');
    }

    public function __construct(MchtFeeChangeHistory $mcht_fee_histories, SfFeeChangeHistory $sf_fee_histories)
    {
        $this->mcht_fee_histories   = $mcht_fee_histories;
        $this->sf_fee_histories     = $sf_fee_histories;
    }

    public function merchandise(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->mcht_fee_histories
            ->join('merchandises', 'mcht_fee_change_histories.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.is_delete', false)
            ->where('mcht_fee_change_histories.is_delete', false)
            ->where('mcht_fee_change_histories.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        if($request->change_status !== null)
            $query = $query->where('mcht_fee_change_histories.change_status', $request->change_status);

        $data = $this->getIndexData($request, $query, 'mcht_fee_change_histories.id', ['mcht_fee_change_histories.*', 'merchandises.mcht_name'], 'mcht_fee_change_histories.created_at');
        return $this->response(0, $data);
    }

    public function salesforce(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->sf_fee_histories
            ->join('merchandises', 'sf_fee_change_histories.mcht_id', '=', 'merchandises.id')
            ->where('merchandises.is_delete', false)
            ->where('sf_fee_change_histories.is_delete', false)
            ->where('sf_fee_change_histories.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");

        if($request->change_status !== null)
            $query = $query->where('sf_fee_change_histories.change_status', $request->change_status);

        $query = $query->with(['bfSales', 'aftSales']);
        $data = $this->getIndexData($request, $query, 'sf_fee_change_histories.id', ['sf_fee_change_histories.*', 'merchandises.mcht_name'], 'sf_fee_change_histories.created_at');
        return $this->response(0, $data);
    }
    
    private function deleteHistory($orm, $target, $id)
    {
        $query      = $orm->where('id', $id);
        $history    = $query->first()->toArray();
        $history_type = $history['change_status'] ? HistoryType::HISTORY_DELETE : HistoryType::BOOK_DELETE;

        $res  = $query->update(['is_delete' => true]);
        operLogging($history_type, $target, $history, $history, "#".$id);
        return $this->response($res ? 4 : 990);
    }

    public function deleteMerchandise(Request $request, int $id)
    {
        return $this->deleteHistory($this->mcht_fee_histories, '가맹점 수수료율', $id);
    }

    public function deleteSalesforce(Request $request, int $id)
    {
        return $this->deleteHistory($this->sf_fee_histories, '영업점 수수료율', $id);
    }

    private function batchDeleteHistory($orm, $target, $ids)
    {
        $query = $orm->whereIn('id', $ids);
        $res = DB::transaction(function () use($query, $target) {
            $res  = $query->update(['is_delete' => true]);    
            $histories = (clone $query)->get();
            foreach($histories as $history)
            {
                $_history = json_decode(json_encode($history), true);
                $history_type = $_history['change_status'] ? HistoryType::HISTORY_DELETE : HistoryType::BOOK_DELETE;
                operLogging($history_type, $target, $_history, $_history, "#".$history['id']);
            }
            return true;
        });
        return $this->response($res ? 4 : 990);
    }

    public function deleteMerchandiseBatch(Request $request)
    {
        return $this->batchDeleteHistory($this->mcht_fee_histories, '가맹점 수수료율', $request->selected_idxs);
    }

    public function deleteSalesforceBatch(Request $request)
    {
        return $this->batchDeleteHistory($this->sf_fee_histories, '영업점 수수료율', $request->selected_idxs);
    }

    /**
     * 가맹점/영업점 수수료율 즉시/예약적용 
     */
    public function apply(Request $request, $user)
    {
        $cond_1 = (Ablilty::isOperator($request) && AuthOperatorIP::valiate($request->user()->brand_id, $request->ip())) || Ablilty::isDevLogin($request);
        $cond_2 = Ablilty::isSalesforce($request);

        if($cond_1 || $cond_2)
        {
            $query = Merchandise::where('id', $request->mcht_id);
            $mcht = (clone $query)->first();

            if(Ablilty::isBrandCheck($request, $mcht->brand_id) === false)
                return $this->response(951);
            else
            {
                $row = MerchandiseFeeUpdater::apply($request, $user, $request->apply_type, $query);
                if($row)
                {
                    if($user == 'merchandises')
                    {
                        $before    = MerchandiseFeeUpdater::getMchtBeforeFee($mcht);
                        $after     = MerchandiseFeeUpdater::getMchtAfterFee($request);
                        $target = '가맹점 수수료율';
                    }
                    else
                    {
                        $before    = MerchandiseFeeUpdater::getSalesBeforeFee($mcht, $request->level);
                        $after     = MerchandiseFeeUpdater::getSalesAfterFee($request);
                        $target = '영업점 수수료율';
                    }
                    if($request->apply_type === 'direct-apply')
                        operLogging(HistoryType::UPDATE, $target, $before, $after, $mcht->mcht_name);
                    else
                        operLogging(HistoryType::BOOK, $target, $before, $after, $mcht->mcht_name);
    
                    return $this->response(0);
                }
                else
                    return $this->response(1000);                
            }
        }
        else
            return $this->response(951);
    }
}
