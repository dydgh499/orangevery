<?php

namespace App\Http\Controllers\Manager\Settle;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Log\SettleDeductMerchandise;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Log\MchtSettleHistoryController;

use App\Http\Requests\Manager\Log\BatchSettleHistoryRequest;
use Illuminate\Http\Request;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\SettleTrait;
use App\Http\Traits\Settle\SettleTerminalTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Traits\Salesforce\UnderSalesTrait;

use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

/**
 * @group Rep-Settle-Merchandise API
 *
 * 대표가맹점 정산 관리 API 입니다.
 */
class RepMerchandiseController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, SettleTrait, SettleTerminalTrait, TransactionTrait, UnderSalesTrait, StoresTrait;
    protected $merchandises, $settleDeducts;

    public function __construct(Merchandise $merchandises, SettleDeductMerchandise $settleDeducts)
    {
        $this->merchandises = $merchandises;
        $this->settleDeducts = $settleDeducts;
    }

    // 대표가맹점 ID 조회
    private function getRepMerchandiseIds($request, $brand_id)
    {
        $acct_nums = $this->merchandises
            ->where('is_delete', 0)
            ->where('brand_id', $brand_id)
            ->groupby('acct_num')
            ->havingRaw('COUNT(acct_num) > 1')
            ->pluck('acct_num')
            ->all();
        $mcht_ids = $this->merchandises
            ->where('is_delete', 0)
            ->where('brand_id', $brand_id)
            ->whereIn('acct_num', $acct_nums)
            ->pluck('id')
            ->all();
            
        return Transaction::noSettlement('mcht_settle_id')
                ->whereIn('mcht_id', $mcht_ids)
                ->distinct()
                ->pluck('mcht_id')
                ->all();
    }

    // 대표가맹점 리스트 조회
    private function getRepMerchandiseContent($request)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo(10);

        $cols = array_merge($this->getDefaultCols(), ['mcht_name', 'use_collect_withdraw', 'withdraw_fee']);
        $cols = $this->getViewableSalesCols($request, $cols);

        $mcht_ids = $this->getRepMerchandiseIds($request, $request->user()->brand_id);
        $query = $this->getDefaultQuery($this->merchandises, $request, $mcht_ids)
            ->where('use_collect_withdraw', false)
            ->where('mcht_name', 'like', "%".$request->search."%")
            ->with(['deducts', 'settlePayModules', 'transactions'])
            ->orderby('acct_num');
    
        $data = $this->getIndexData($request, $query, 'id', $cols, "created_at", false);
        $data = $this->getSettleInformation($data, $target_settle_amount);
        $data = $this->setTerminalCost($data, $request->s_dt, $request->e_dt, 'mcht_id');
        $data = $this->setSettleFinalAmount($data, $this->getCancelDeposits($request->s_dt, $request->e_dt, $data));
        return json_decode(json_encode($data), true);
    }

    // 계좌번호 그룹 세팅
    private function setAcctNumSettleGroup($idx, $acct_num_groups, $content)
    {
        if($idx !== false)
        {
            $acct_num_groups[$idx]['total_amount'] += $content['total']['amount'];
            $acct_num_groups[$idx]['cxl_amount'] += $content['cxl']['amount'];
            $acct_num_groups[$idx]['cxl_count'] += $content['cxl']['count'];
            $acct_num_groups[$idx]['appr_amount'] += $content['appr']['amount'];
            $acct_num_groups[$idx]['appr_count'] += $content['appr']['count'];
            $acct_num_groups[$idx]['deduct_amount'] += $content['deduction']['amount'];
            $acct_num_groups[$idx]['settle_amount'] += $content['settle']['amount'];
            $acct_num_groups[$idx]['trx_amount'] += $content['total']['total_trx_amount'];

            $acct_num_groups[$idx]['settle_fee'] += $content['total']['settle_fee'];
            $acct_num_groups[$idx]['comm_settle_amount'] += $content['terminal']['amount'];
            $acct_num_groups[$idx]['under_sales_amount'] += $content['terminal']['under_sales_amount'];
            $acct_num_groups[$idx]['cancel_deposit_amount'] = $content['settle']['cancel_deposit_amount'];

            $acct_num_groups[$idx]['cancel_deposit_idxs'] = array_merge($acct_num_groups[$idx]['cancel_deposit_idxs'], $content['cancel_deposit_idxs']);
            $acct_num_groups[$idx]['settle_transaction_idxs'] = array_merge($acct_num_groups[$idx]['settle_transaction_idxs'], $content['settle_transaction_idxs']);
            $acct_num_groups[$idx]['settle_pay_module_idxs'] = array_merge($acct_num_groups[$idx]['settle_pay_module_idxs'], $content['terminal']['settle_pay_module_idxs']);
        }
        else
        {
            $acct_num_groups[] = [
                'id' => $content['id'],
                'acct_name' => $content['acct_name'],
                'acct_num' => $content['acct_num'],
                'acct_bank_code' => $content['acct_bank_code'],
                'acct_bank_name' => $content['acct_bank_name'],
                'total_amount' => $content['total']['amount'],    // 총 매출액
                'cxl_amount' => $content['cxl']['amount'],        // 총 취소액
                'cxl_count' => $content['cxl']['count'],
                'appr_amount' => $content['appr']['amount'],      // 총 승인액
                'appr_count' => $content['appr']['count'],
                'deduct_amount' => $content['deduction']['amount'], // 추가차감금
                'settle_amount' => $content['settle']['amount'],    // 정산액
                'trx_amount' => $content['total']['total_trx_amount'],    // 총 거래 수수료(매출)
                'level' => 10,
                'settle_fee' => $content['total']['settle_fee'],
                'comm_settle_amount' => $content['terminal']['amount'],
                'under_sales_amount' => $content['terminal']['under_sales_amount'],
                'cancel_deposit_amount' => $content['settle']['cancel_deposit_amount'],
                'cancel_deposit_idxs' => $content['cancel_deposit_idxs'],
                'settle_transaction_idxs' => $content['settle_transaction_idxs'],
                'settle_pay_module_idxs' => $content['terminal']['settle_pay_module_idxs'],
            ];
        }
        return $acct_num_groups;
    }

    // 계좌번호 별로 그룹화
    private function splitAcctNumSettleGroup($data, $e_dt, $brand_id)
    {
        $add_deducts = [];
        $acct_num_groups = [];

        for ($i=0; $i <count($data['content']); $i++) 
        {
            $content = $data['content'][$i];
            $content['acct_num'] = str_replace('-', '', trim($content['acct_num']));

            $idx = array_search($content['acct_num'], array_column($acct_num_groups, 'acct_num'));
            $acct_num_groups = $this->setAcctNumSettleGroup($idx, $acct_num_groups, $content);

            if($idx !== false)
            {   // 추가차감 적용
                $add_deducts[] = [
                    'amount' => $content['settle']['amount'],
                    'mcht_id' => $content['id'],
                    'deduct_dt' => $e_dt,
                    'brand_id'  => $brand_id,
                ];
            }
        }
        
        $acct_num_groups = array_filter($acct_num_groups, function($acct_num_group) {
            return $acct_num_group['settle_amount'] > 0;
        });
        $add_deducts = array_filter($add_deducts, function($add_deduct) use($acct_num_groups) {
            return  array_search($add_deduct['mcht_id'], array_column($acct_num_groups, 'id')) !== false;
        });

        return [$acct_num_groups, $add_deducts];
    }

    public function settlement(Request $request)
    {
        $validated = $request->validate(['s_dt'=>'required|date', 'e_dt'=>'required|date']);
        $data = $this->getRepMerchandiseContent($request);
        [$acct_num_groups, $add_deducts] = $this->splitAcctNumSettleGroup($data, $request->e_dt, $request->user()->brand_id);

        $req = new BatchSettleHistoryRequest([
            'datas' => $acct_num_groups,
            'e_dt' => $request->e_dt,
        ]);
        logging($acct_num_groups);
        $req->setUserResolver(function () use ($request) {
            return $request->user();
        });

        $response = resolve(MchtSettleHistoryController::class)->batch($req);
        #$res = $this->manyInsert($this->settleDeducts, $add_deducts);
        return $response;
    }
}
