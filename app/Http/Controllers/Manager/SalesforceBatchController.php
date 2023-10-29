<?php

namespace App\Http\Controllers\Manager;

use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Models\NotiUrl;
use App\Models\Log\SfFeeChangeHistory;
use App\Models\Log\SfFeeApplyHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Salesforce-Batch API
 *
 * 영업자의 일괄적용 group 입니다.
 */
class SalesforceBatchController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $merchandises, $payModules;

    public function __construct(Merchandise $merchandises, PaymentModule $payModules)
    {
        $this->merchandises = $merchandises;
        $this->payModules   = $payModules;
    }

    private function payModuleBatch($request)
    {
        $sales_key = $this->getSalesKeys($request);
        $sales_id = 'merchandises.'.$sales_key['sales_id'];
        $query = $this->payModules
            ->join('merchandises', 'payment_modules.mcht_id', '=', 'merchandises.id')
            ->where('payment_modules.brand_id', $request->user()->brand_id)
            ->where($sales_id, $request->id);
        if($request->pg_id)
            $query = $query->where('payment_modules.pg_id', $request->pg_id);
        return $query;
    }

    private function merchandiseBatch($request)
    {
        $sales_key = $this->getSalesKeys($request);
        return $this->merchandises->where('brand_id', $request->user()->brand_id)
            ->where($sales_key['sales_id'], $request->id);
    }

    private function getSalesKeys($request)
    {
        $idx  = globalLevelByIndex($request->level);
        $sales_key = [
            'sales_fee' => 'sales'.$idx.'_fee',
            'sales_id'  => 'sales'.$idx.'_id',
        ];
        return $sales_key;
    }

    /**
     * 수수료율 적용 
     */
    public function setFee(Request $request)
    {
        $sales_key = $this->getSalesKeys($request);
        $aft_trx_fee = $request->sales_fee/100;
        $aft_sales_id = $request->id;

        $mchts = $this->merchandises->where('brand_id', $request->user()->brand_id)
                ->where($sales_key['sales_id'], $request->id)
                ->get();

        $datas = [];
        foreach($mchts as $mcht)
        {
            $js_mcht = json_decode(json_encode($mcht), true);
            $bf_trx_fee = $js_mcht[$sales_key['sales_fee']];
            $bf_sales_id = $mcht[$sales_key['sales_id']];            
            array_push($datas, [
                'brand_id' => $request->user()->brand_id,
                'mcht_id'   => $js_mcht['id'],
                'level'     => $request->level,
                'bf_trx_fee' => $bf_trx_fee,
                'aft_trx_fee' => $aft_trx_fee,
                'bf_sales_id' => $bf_sales_id,
                'aft_sales_id' => $aft_sales_id,
                'change_status' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $res = DB::transaction(function () use($sales_key, $datas, $request, $aft_sales_id, $aft_trx_fee) {
            $u_m_res = $this->merchandises->where('brand_id', $request->user()->brand_id)
                ->where($sales_key['sales_id'], $request->id)
                ->update([$sales_key['sales_fee'] => $request->sales_fee/100]);

            $i_chg_res = $this->manyInsert(new SfFeeChangeHistory(), $datas);

            $u_apply_res = SfFeeApplyHistory::where('sales_id', $aft_sales_id)
                ->update(['is_delete' => true]);
            $c_apply_res = SfFeeApplyHistory::create([
                'brand_id' => $request->user()->brand_id,
                'sales_id' => $aft_sales_id,
                'trx_fee'  => $aft_trx_fee,
            ]);
            return $i_chg_res && $c_apply_res;
        });
        return $this->response($res ? 1 : 990);
    }

    /**
     * 커스텀 필터 적용 
     */
    public function setCustomFilter(Request $request)
    {
        $cols = ['custom_id' => $request->custom_id];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 계좌정보 적용 
    */
    public function setAccountInfo(Request $request)
    {
        $cols = [
            'acct_num' => $request->acct_num,
            'acct_name' => $request->acct_name,
            'acct_bank_code' => $request->acct_bank_code,
            'acct_bank_name' => $request->acct_bank_name,
        ];
        $row = $this->merchandiseBatch($request)->update($cols);
        return $this->response(1);
    }
    
    /**
     * 이상거래 한도 적용 
     */
    public function setAbnormalTransLimit(Request $request)
    {
        $cols = [
            'payment_modules.abnormal_trans_limit' => $request->abnormal_trans_limit
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 중복결제 허용회수 적용 
     */
    public function setDupPayCountValidation(Request $request)
    {
        $cols = [
            'payment_modules.pay_dupe_limit' => $request->pay_dupe_limit
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 중복결제 하한금 적용 
     */
    public function setDupPayLeastValidation(Request $request)
    {
        $cols = [
            'payment_modules.pay_dupe_least' => $request->pay_dupe_least
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제한도 적용 
     */
    public function setPayLimit(Request $request)
    {
        $type = 'pay_'.$request->type.'_limit';
        if($request->type == 'day')
            $limit = $request->pay_day_limit;
        else if($request->type == 'month')
            $limit = $request->pay_month_limit;
        else if($request->type == 'year')
            $limit = $request->pay_year_limit;
        else if($request->type == 'single')
            $limit = $request->pay_single_limit;
        
        $cols = [
            'payment_modules.'.$type => $limit
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제금지시간 적용 
     */
    public function setForbiddenPayTime(Request $request)
    {
        $cols = [
            'payment_modules.pay_disable_s_tm' => $request->pay_disable_s_tm,
            'payment_modules.pay_disable_e_tm' => $request->pay_disable_e_tm,
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 결제창 노출여부 적용 
     */
    public function setShowPayView(Request $request)
    {
        $cols = [
            'payment_modules.show_pay_view' => $request->show_pay_view,
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * 노티 URL 일괄등록
     *
     * 가맹점 이상 가능
     *
     */
    public function setNotiUrl(Request $request)
    {
        $validated = $request->validate([
            'send_url'=>'required',
        ]);
        
        $notis = new NotiUrl();        
        $mcht_ids = $this->merchandiseBatch($request)->get(['id'])->pluck('id');
        $registered_notis = $notis
            ->whereIn('mcht_id', $mcht_ids)
            ->where('send_url', $request->send_url)
            ->get(['mcht_id']);

        $datas = [];
        foreach($mcht_ids as $mcht_id)
        {
            $registered = $registered_notis->first(function($noti) use ($mcht_id) {
                return $noti->mcht_id == $mcht_id;
            });
            if($registered)
            {
            }
            else
            {
                array_push($datas, [
                    'brand_id' => $request->user()->brand_id,
                    'mcht_id' => $mcht_id,
                    'send_url' => $request->send_url,
                    'noti_status' => $request->noti_status,
                    'note' => $request->note,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $res = $this->manyInsert($notis, $datas);
        $u_res = $this->merchandises->whereIn('id', $mcht_ids)->update(['use_noti' => true]);

        return $this->response(1, ['registered_notis'=>$registered_notis->pluck('id'), 'count'=>count($datas)]);
    }

    /**
     * MID 일괄적용
     *
     * 가맹점 이상 가능
     *
     */
    public function setMid(Request $request)
    {
        $cols = [
            'payment_modules.mid' => $request->mid,
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * TID 일괄적용
     *
     * 가맹점 이상 가능
     *
     */
    public function setTid(Request $request)
    {
        $cols = [
            'payment_modules.tid' => $request->tid,
        ];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * API KEY 일괄적용
     *
     * 가맹점 이상 가능
     *
     */
    public function setApiKey(Request $request)
    {
        $cols = ['payment_modules.api_key' => $request->api_key,];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }

    /**
     * SUB KEY 일괄적용
     *
     * 가맹점 이상 가능
     */
    public function setSubKey(Request $request)
    {
        $cols = ['payment_modules.sub_key' => $request->sub_key];
        $row = $this->payModuleBatch($request)->update($cols);
        return $this->response(1);
    }
}
