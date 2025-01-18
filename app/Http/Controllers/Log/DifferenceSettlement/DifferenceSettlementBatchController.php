<?php

namespace App\Http\Controllers\Log\DifferenceSettlement;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Log\SubBusinessRegistration;
use App\Models\Log\DifferenceSettlementHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\StoresTrait;

/**
 * @group Difference-Settlement-History API
 *
 * 차액정산 이력 API 입니다.
 */
class DifferenceSettlementBatchController extends Controller
{
    use StoresTrait;
    public $base_path = "App\Http\Controllers\Log\DifferenceSettlement\\";

    public function getUseDifferentSettlementBrands()
    {
        return Brand::join('different_settlement_infos', 'brands.id', '=', 'different_settlement_infos.brand_id')
            ->where('brands.is_delete', false)
            ->where('brands.use_different_settlement', true)
            ->where('different_settlement_infos.is_delete', false)
            ->get(['brands.business_num', 'different_settlement_infos.*']);
    }

    public function getPGClass($brand)
    {
        try
        {
            $pg_name = getPGType($brand->pg_type);
            $path   = $this->base_path.$pg_name;
            $pg     = new $path(json_decode(json_encode($brand), true));
        }
        catch(Exception $e)
        {   // pg사 발견못함
            $pg     = null;
            logging([
                    'message' => $e->getMessage(),
                    'brand' => json_decode(json_encode($brands[$i]), true),
                ],
                'PG사가 없습니다.'
            );
        }
        return $pg;
    }

    public function getRequestTransIds($brand, $start_day, $end_day)
    {
        // 성공 거래건
        $new_ids    = Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where('transactions.brand_id', $brand->brand_id)
            ->where('transactions.trx_at', '>=', $start_day." 00:00:00")
            ->where('transactions.trx_at', '<=', $end_day." 23:59:59")
            ->pluck('transactions.id')
            ->all();

        // 재시도 거래건
        $start_of_year  = Carbon::now()->startOfYear()->toDateTimeString();
        $retry_ids  = Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where('transactions.brand_id', $brand->brand_id)
            ->where('transactions.trx_at', '>=', $start_of_year)   // index
            ->where('difference_settlement_histories.settle_result_code', '51')
            ->pluck('transactions.id')
            ->all();

        return array_unique(
            array_merge(
                $new_ids, 
                $retry_ids
            )
        );
    }

    public function filterDuplicateTransIds($full_histories)
    {
        $exist_trans_ids = DifferenceSettlementHistory::whereIn('trans_id', array_column($full_histories, 'trans_id'))
            ->pluck('trans_id')
            ->all();
        if(count($exist_trans_ids))
        {
            $filtered_histories = array_values(array_filter($full_histories, function ($history) use ($exist_trans_ids) {
                return in_array($history['trans_id'], $exist_trans_ids) === false;
            }));
            error([
                'filtered_before' => count($exist_trans_ids), 'filtered_after' => count($filtered_histories)
            ],'exist already uploaded transactions');
            return $filtered_histories;
        }
        else
            return $full_histories;
    }

    public function differenceSettlementRequestProcess($brand, $date, $ids)
    {
        $results = collect();
        foreach(array_chunk($ids, 1000) as $chunk)
        {
            $partial_results = Transaction::join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
                ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
                ->whereIn('transactions.id', $chunk)
                ->where('payment_modules.use_different_settlement', 1)
                ->get([
                    'transactions.id', 'transactions.ord_num', 
                    'transactions.is_cancel', 'transactions.cxl_seq', 
                    'transactions.cxl_dt', 'transactions.trx_dt', 
                    'transactions.trx_id', 'transactions.ori_trx_id', 
                    'transactions.mid', 'transactions.amount', 
                    'merchandises.business_num', 'payment_modules.p_mid', 
                ]);
            $results = $results->concat($partial_results);
        }

        $pg = $this->getPGClass($brand);
        if($pg)
        {
            $full_histories = $pg->request($date, $results);
            $full_histories = $this->filterDuplicateTransIds($pg->request($date, $results));
            $res = $this->manyInsert(new DifferenceSettlementHistory, $full_histories);
        }
    }

    public function differenceSettleRequest()
    {
        $brands     = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');
        logging([], "difference-settlement-request (".$yesterday.")");

        foreach($brands as $brand) 
        {
            $ids = $this->getRequestTransIds($brand, $yesterday, $yesterday);
            $this->differenceSettlementRequestProcess($brand, $date, $ids);
        }
    }

    public function getResponseSuccessFormat($histores, $items)
    {
        $datas = [];
        $items_by_trans_id = [];
        // 성능향상 O(1)
        foreach ($items as $item) 
        {
            $items_by_trans_id[$item['trans_id']] = $item;
        }
        foreach ($histores as $history)
        {
            if (isset($items_by_trans_id[$history->trans_id])) 
            {
                $item = $items_by_trans_id[$history->trans_id];
                $datas[] = [
                    'trans_id'           => $item['trans_id'],
                    'settle_result_code' => $item['settle_result_code'],
                    'settle_result_msg'  => $item['settle_result_msg'],
                    'updated_at'         => $item['updated_at'],
                    'mcht_section_code'  => $item['mcht_section_code'],
                    'req_dt'             => $history->req_dt,
                    'settle_dt'          => $item['settle_dt'],
                    'supply_amount'      => $item['supply_amount'],
                    'vat_amount'         => $item['vat_amount'],
                    'settle_amount'      => $item['settle_amount'],
                    'created_at'         => $history->created_at,
                    'updated_at'         => $item['updated_at'],
                ];
            }
        }
        return $datas;
    }

    public function differenceSettlementResponseProcess($brand, $date)
    {
        $pg = $this->getPGClass($brand);
        if($pg)
        {
            $groups  = $pg->response($date);
            foreach($groups as $code => $items)
            {
                DB::transaction(function () use($code, $items) {
                    $ids = array_column($items, 'trans_id');
                    if(count($ids))
                    {
                        if($code === '00')
                        {
                            $query  = DifferenceSettlementHistory::whereIn('trans_id', $ids);
                            $exist_tran = (clone $query)->get();
                            $datas  = $this->getResponseSuccessFormat($exist_tran, $items);

                            $query->delete();
                            logging(['delete' => count($exist_tran), 'add' => count($datas)], "difference-settlement-response-add (O)");
                            $this->manyInsert(new DifferenceSettlementHistory, $datas);
                        }
                        else
                        {
                            DifferenceSettlementHistory::whereIn('trans_id', $ids)
                                ->update([
                                    'settle_result_code' => $items[0]['settle_result_code'],
                                    'settle_result_msg' => $items[0]['settle_result_msg'],
                                    'mcht_section_code' => $items[0]['mcht_section_code'],
                                    'updated_at' => $items[0]['updated_at'],
                                ]);
                        }
                    }
                });
            }
        }
    }

    public function differenceSettleResponse()
    {
        $brands = $this->getUseDifferentSettlementBrands();
        $date   = Carbon::now();
        logging([], "difference-settlement-response (".$date->format('Y-m-d').")");

        foreach($brands as $brand)
        {
            $this->differenceSettlementResponseProcess($brand, $date);
        }
    }

    /*
    * 차액정산 가맹점 정보 등록
    */
    public function differenceSettleRegisterRequest()
    {
        $brands     = $this->getUseDifferentSettlementBrands();
        $date       = Carbon::now();

        for ($i=0; $i<count($brands); $i++)
        {
            $pg = $this->getPGClass($brands[$i]);
            if($pg)
            {
                $sub_business_regi_infos = SubBusinessRegistration::where('registration_result', -1)
                    ->where('brand_id', $brands[$i]->brand_id)
                    ->where('pg_type', $brands[$i]->pg_type)
                    ->orderby('business_num')
                    ->get();
                $cols = [
                    'merchandises.id', 'merchandises.sector', 'merchandises.business_num',
                    'merchandises.mcht_name','merchandises.addr',
                    'merchandises.nick_name','merchandises.phone_num',
                    'merchandises.email','merchandises.website_url',
                ];
                $query = Merchandise::where('merchandises.brand_id', $brands[$i]->brand_id)
                    ->whereIn('merchandises.business_num', $sub_business_regi_infos->pluck('business_num')->all())
                    ->where('merchandises.is_delete', false);
                if($brands[$i]->pg_type === 22)
                {
                    $query = $query->join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
                            ->where('payment_modules.p_mid', '!=', '');
                    $cols[] = 'payment_modules.p_mid';
                }

                $mchts = $query->orderby('merchandises.updated_at', 'desc')->get($cols);
                $res = $pg->registerRequest($date, $mchts, $sub_business_regi_infos);
                if($res)
                    SubBusinessRegistration::whereIn('id', $sub_business_regi_infos->pluck('id')->all())->update(['registration_result'=>-5]);                    
            }
        }
    }

    /*
    * 차액정산 가맹점 정보 등록 결과
    */
    public function differenceSettleResisterResponse()
    {
        $pay_gateways = $this->getUseDifferentSettlementPayGateways();
        $brand  = Brand::first(['business_num']);
        $date   = Carbon::now()->addDay(4);

        for ($i=0; $i<count($pay_gateways); $i++)
        {
            $pay_gateways[$i]->business_num = $brand->business_num;
            $pg = $this->getPGClass($pay_gateways[$i]);
            if($pg)
            {
                $datas  = $pg->registerResponse($date);
                foreach($datas as $data)
                {
                    if(isset($data['where']['id']))
                        SubBusinessRegistration::where('id', $data['where']['id'])->update($data['update']);
                    else
                    {
                        SubBusinessRegistration::where('pg_type', $data['pg_type'])
                            -where('business_num', $data['where']['business_num'])
                            -where('card_company_code', $data['where']['card_company_code'])
                            ->update($data['update']);
                    }
                }
            }
        }
    }

    public function getNotApplyTransactionIds($brand, $yesterday, $start_day, $end_day)
    {
        // 성공건 조회
        $success_trans_query  = Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->join('difference_settlement_histories', 'transactions.id', '=', 'difference_settlement_histories.trans_id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where('transactions.brand_id', $brand->brand_id)
            ->where(function ($query) use ($yesterday, $start_day, $end_day) {
                $yesterday_s_dt = "{$yesterday} 00:00:00";
                $yesterday_e_dt = "{$yesterday} 23:59:59";
                $s_dt = "{$start_day} 00:00:00";
                $e_dt = "{$end_day} 23:59:59";                
                $query
                    ->whereBetween('transactions.trx_at', [$yesterday_s_dt, $yesterday_e_dt])
                    ->orWhereBetween('transactions.trx_at', [$s_dt, $e_dt]);
            })
            ->where(function ($query) {
                $query
                    ->where('difference_settlement_histories.settle_result_code', '00')
                    ->orWhere('difference_settlement_histories.settle_result_code', '0000');
            })
            ->select('transactions.id');

        // 미반영 거래건 조회
        return Transaction::join('payment_gateways', 'transactions.pg_id', '=', 'payment_gateways.id')
            ->where('payment_gateways.pg_type', $brand->pg_type)
            ->where('transactions.brand_id', $brand->brand_id)
            ->where(function ($query) use ($yesterday, $start_day, $end_day) {
                $yesterday_s_dt = "{$yesterday} 00:00:00";
                $yesterday_e_dt = "{$yesterday} 23:59:59";
                $s_dt = "{$start_day} 00:00:00";
                $e_dt = "{$end_day} 23:59:59";                
                $query
                    ->whereBetween('transactions.trx_at', [$yesterday_s_dt, $yesterday_e_dt])
                    ->orWhereBetween('transactions.trx_at', [$s_dt, $e_dt]);
            })
            ->whereNotIn('transactions.id', $success_trans_query)
            ->pluck('transactions.id')
            ->all();
    }

    /*
    * 차액정산 테스트 업로드
    */
    static public function differenceSettleRequestTest($ds_ids, $start_days, $end_days)
    {
        //DifferenceSettlementBatchController::differenceSettleRequestTest([2], 60, 1)
        $inst       = new DifferenceSettlementBatchController(new DifferenceSettlementHistory);
        $date       = Carbon::now();
        $yesterday  = $date->copy()->subDay(1)->format('Y-m-d');
        $start_day  = $date->copy()->subDay($start_days)->format('Y-m-d');
        $end_day    = $date->copy()->subDay($end_days)->format('Y-m-d');
        logging(['ds_ids' => $ds_ids], "difference-settlement-request-test ($start_day~$end_day, $yesterday)");

        $brands     = $inst->getUseDifferentSettlementBrands();
        foreach($brands as $brand)
        {
            if(in_array($brand->id, $ds_ids))
            {
                $ids = $inst->getNotApplyTransactionIds($brand, $yesterday, $start_day, $end_day);
                $inst->differenceSettlementRequestProcess($brand, $date, $ids);
            }
        }
    }

    static public function differenceSettleResponseTest($ds_id, $sub_day)
    {
        $inst   = new DifferenceSettlementBatchController(new DifferenceSettlementHistory);
        $date   = Carbon::now()->subDay($sub_day);
        logging(['ds_id' => $ds_id], "difference-settlement-response-test (".$date->format('Y-m-d').")");
        $brands = $inst->getUseDifferentSettlementBrands();
        foreach($brands as $brand)
        {
            if($brand->id === $ds_id)
            {
                $inst->differenceSettlementResponseProcess($brand, $date);
            }
        }
    }
}
