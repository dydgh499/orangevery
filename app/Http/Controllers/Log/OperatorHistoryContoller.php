<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\OperatorHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use App\Models\Options\PvOptions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Operator-History API
 *
 * 운영자 활동이력 API 입니다.
 */
class OperatorHistoryContoller extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $operator_histories;

    public function __construct(OperatorHistory $operator_histories)
    {
        // 가맹점, 결제모듈, 영업점, 구분정보, 수수료율, 매출, 금융 VAN
        $this->operator_histories = $operator_histories;
    }
    
    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $cols = [
            'operators.nick_name',
            'operator_histories.*',
        ];
        $query  = $this->operator_histories
            ->join('operators', 'operator_histories.oper_id', '=', 'operators.id')
            ->where('operator_histories.brand_id', $request->user()->brand_id);
            
        if($request->history_type !== null)
            $query = $query->where('operator_histories.history_type', $request->history_type);
        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('operators.nick_name', 'like', "%$search%")
                    ->orWhere('operator_histories.history_title', 'like', "%$search%")
                    ->orWhere('operator_histories.after_history_detail', 'like', "%$search%");
            });
        }

        $data = $this->getIndexData($request, $query, 'operator_histories.id', $this->operator_histories->cols, 'operator_histories.created_at');
        return $this->response(0, $data);
    }

    public static function logging(Request $request)
    {
        $instance = new OperatorHistoryContoller(new OperatorHistory);
        return $instance->store($request);
    }
    /**
     * 추가
     *
     * 운영자 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(Request $request)
    {
        $data = [
            'brand_id' => $request->brand_id ? $request->brand_id : $request->user()->brand_id,
            'oper_id' => $request->oper_id ? $request->oper_id : $request->user()->id,
            'history_type' => $request->history_type,
            'history_title' => $request->history_title,
            'history_target' => $request->history_target,
            'before_history_detail' => $request->before_history_detail,
            'after_history_detail' => $request->after_history_detail,
        ];
        return $this->operator_histories->create($data);
    }

    private function paidOptionFilter($data, $history_detail)
    {
        if(strlen($history_detail))
        {
            $conv_history_detail = json_decode($history_detail, true);
            $pv_options = new PvOptions($data->pv_options);
            
            if($pv_options->paid->use_issuer_filter === false)
                unset($conv_history_detail['filter_issuers']);
            if($pv_options->paid->use_forb_pay_time === false)
            {
                unset($conv_history_detail['pay_disable_s_tm']);   
                unset($conv_history_detail['pay_disable_e_tm']);            
            }
            if($pv_options->paid->use_pay_limit === false)
            {
                unset($conv_history_detail['pay_year_limit']);   
                unset($conv_history_detail['pay_month_limit']);   
                unset($conv_history_detail['pay_day_limit']);
                unset($conv_history_detail['pay_single_limit']);            
            }
            if($pv_options->paid->use_regular_card === false)
                unset($conv_history_detail['use_regular_card']);
            if($pv_options->paid->use_collect_withdraw === false)
                unset($conv_history_detail['use_collect_withdraw']);  
            if($pv_options->paid->use_noti === false)
                unset($conv_history_detail['use_noti']);
            if(isset($conv_history_detail['brand_id']))
                unset($conv_history_detail['brand_id']);
            if($pv_options->paid->use_collect_withdraw === false)
                unset($conv_history_detail['collect_withdraw_fee']);
            
            if($pv_options->paid->use_hide_account === false)
                unset($conv_history_detail['is_hide_account']);
    
            if($pv_options->paid->use_multiple_hand_pay === false)
                unset($conv_history_detail['use_multiple_hand_pay']);    
            if($pv_options->paid->use_pay_verification_mobile === false)
                unset($conv_history_detail['use_pay_verification_mobile']);
    
            if($pv_options->paid->use_settle_hold === false)
            {
                unset($conv_history_detail['settle_hold_s_dt']);
                unset($conv_history_detail['settle_hold_reason']);
            }
            if($pv_options->paid->use_noti === false)
                unset($conv_history_detail['use_noti']);
            
            if($data->use_different_settlement === false)
            {
                unset($conv_history_detail['website_url']);
                unset($conv_history_detail['email']);
            }
            if($pv_options->paid->use_pmid === false)
                unset($conv_history_detail['p_mid']);
    
            unset($conv_history_detail['user_pw']);
    
            $history_detail = [];
            foreach($conv_history_detail as $key => $value)
            {
                $history_detail[__('validation.attributes.'.$key)] = $conv_history_detail[$key];
            }
            return $history_detail;
        }
        else
            return [];

    }

    /**
     * 단일조회(상세조회)
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 영업자 이력 PK
     */
    public function show($id)
    {
        $data = $this->operator_histories
            ->join('operators', 'operator_histories.oper_id', '=', 'operators.id')
            ->join('brands', 'operator_histories.brand_id', '=', 'brands.id')
            ->where('operator_histories.id', $id)
            ->first(['operator_histories.*', 'operators.nick_name', 'brands.pv_options', 'brands.use_different_settlement']);

        $data->before_history_detail = $this->paidOptionFilter($data, $data->before_history_detail);
        $data->after_history_detail  = $this->paidOptionFilter($data, $data->after_history_detail);
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 이상거래 PK
     */
    public function destroy($id)
    {
        //
    }
}
