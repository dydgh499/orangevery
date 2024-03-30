<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\MchtFeeChangeHistory;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Merchandise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    use ManagerTrait, ExtendResponseTrait;
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

        logging([
            'mcht_histories'=> json_decode(json_encode($mcht_histories), true),
            'sf_histories'  => json_decode(json_encode($sf_histories), true),
        ], 'fee-book-apply-scheduler');

        $date = date('Y-m-d H:i:s');
        foreach($sf_histories as $sf_history)
        {
            DB::transaction(function () use($sf_history, $date) {
                $idx = globalLevelByIndex($sf_history->level);
                Merchandise::where('id', $sf_history->mcht_id)
                    ->update([
                        'sales'.$idx.'_id'  => $sf_history->aft_sales_id,
                        'sales'.$idx.'_fee' => $sf_history->aft_trx_fee/100,
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
        return true;
        logging(['result' => true], 'fee-book-apply-scheduler');
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

    public function deleteMerchandise(Request $request, $id)
    {
        return $this->deleteHistory($this->mcht_fee_histories, '가맹점 수수료율', $id);
    }

    public function deleteSalesforce(Request $request, $id)
    {
        return $this->deleteHistory($this->sf_fee_histories, '영업점 수수료율', $id);
    }

    private function getMchtResource($request, $mcht, $data)
    {
        $udpt_data = [];
        $bf_trx_fee = $mcht->trx_fee;
        $aft_trx_fee = round($request->trx_fee/100, 7);
        $bf_hold_fee = $mcht->hold_fee;
        $aft_hold_fee = round($request->hold_fee/100, 7);

        $data['apply_dt']    = $request->apply_dt;
        $data['bf_trx_fee']  = $bf_trx_fee;
        $data['aft_trx_fee'] = $aft_trx_fee;
        $udpt_data['trx_fee'] = $aft_trx_fee;

        $data['bf_hold_fee'] = $bf_hold_fee;
        $data['aft_hold_fee'] = $aft_hold_fee;
        $udpt_data['hold_fee'] = $aft_hold_fee;

        return [
            'history' => $data,
            'update'  => $udpt_data,
            'before'  => [
                'trx_fee' => $bf_trx_fee,
                'hold_fee' => $bf_hold_fee,
            ]
        ];
    }

    private function getSalesResource($request, $mcht, $data)
    {
        $udpt_data  = [];
        $data['level'] = $request->level;
        $idx  = globalLevelByIndex($data['level']);
        $mcht = json_decode(json_encode($mcht), true);
        $sales_key = [
            'sales_fee' => 'sales'.$idx.'_fee',
            'sales_id'  => 'sales'.$idx.'_id',
        ];
        
        $bf_trx_fee = $mcht[$sales_key['sales_fee']];
        $aft_trx_fee = round($request->sales_fee/100, 7);
        $bf_sales_id = $mcht[$sales_key['sales_id']];
        $aft_sales_id = $request->sales_id;

        $data['apply_dt']       = $request->apply_dt;
        $data['bf_trx_fee']     = $bf_trx_fee;
        $data['aft_trx_fee']    = $aft_trx_fee;
        $udpt_data[$sales_key['sales_fee']] = $aft_trx_fee;

        $data['bf_sales_id'] = $bf_sales_id;
        $data['aft_sales_id'] = $aft_sales_id;
        $udpt_data[$sales_key['sales_id']] = $aft_sales_id;
        
        return [
            'history' => $data,
            'update'  => $udpt_data,
            'before'  => [
                $sales_key['sales_id']=> $bf_sales_id,
                $sales_key['sales_fee'] => $bf_trx_fee,
            ]
        ];
    }

    public function apply(Request $request, $user, $type)
    {
        $change_status = $type == 'direct-apply' ? 1 : 0;
        $data = [
            'mcht_id'   => $request->mcht_id,
            'brand_id'  => $request->user()->brand_id,
            'change_status' => $change_status,
        ];
        $mchts  = new Merchandise();
        $mcht   = $mchts->where('id', $request->mcht_id)->first();
        if($mcht)
        {
            if($user == 'merchandises')
            {
                $resource = $this->getMchtResource($request, $mcht, $data);
                $orm = $this->mcht_fee_histories;
                $target = '가맹점 수수료율';
            }
            else
            {
                $resource = $this->getSalesResource($request, $mcht, $data);
                $orm = $this->sf_fee_histories;
                $target = '영업점 수수료율';
            }
            
            $res = DB::transaction(function () use($orm, $resource, $mcht, $mchts, $target) {
                $res = $orm->create($resource['history']);
                if($resource['history']['change_status'] == true)
                {
                    $res = $mchts->where('id', $resource['history']['mcht_id'])->update($resource['update']);
                    operLogging(HistoryType::UPDATE, $target, $resource['before'], $resource['update'], $mcht->mcht_name);
                }
                else
                    operLogging(HistoryType::BOOK, $target, $resource['before'], $resource['update'], $mcht->mcht_name);

                return true;
            }, 3);
            return $this->response(0);
        }
        else
            return $this->response(1000);
    }
}
