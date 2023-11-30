<?php

namespace App\Http\Controllers\Manager;

use App\Models\PaymentModule;
use App\Models\CollectWithdraw;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\CollectWithdrawRequest;
use App\Http\Requests\Manager\IndexRequest;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Collect Withdraw deposit API
 *
 * 모아서 출금 API 입니다.
 */
class CollectWithdrawController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $collect_withdraws;

    public function __construct(CollectWithdraw $collect_withdraws)
    {
        $this->collect_withdraws = $collect_withdraws;
        $this->base_noti_url = env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes';
    }

    public function commonSelect(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query = $this->collect_withdraws->join('merchandises', 'merchandises.id', '=', 'collect_withdraws.mcht_id')
            ->where('collect_withdraws.brand_id', $request->user()->brand_id)
            ->where('merchandises.mcht_name', 'like', "%$search%");            
        $query = globalSalesFilter($query, $request, 'merchandises');
        return $query;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexRequest $request)
    {
        $cols = [
            'collect_withdraws.*',
            'merchandises.mcht_name',
        ];
        $query = $this->commonSelect($request);
        return $this->getIndexData($request, $query, 'collect_withdraws.id', $cols, 'collect_withdraws.created_at');
    }

    /**
     * 추가
     *
     * 가맹점만 가능
     *
     */
    public function store(CollectWithdrawRequest $request)
    {
        $pay_modules = PaymentModule::where('mcht_id', $request->user()->id)
            ->where('is_delete', false)
            ->get(['fin_id', 'withdraw_fee', 'use_realtime_deposit']);

        $fin_module = $pay_modules->first(function ($pay_module) {
            return $pay_module->fin_id > 0 && $pay_module->use_realtime_deposit;
        });
        $fin_id = $fin_module ? $fin_module->fin_id : 0;
        if($fin_id)
        {
            $params = [
                'brand_id' => $request->user()->brand_id,
                'mcht_id' => $request->user()->id,
                'withdraw_amount' => $request->withdraw_amount,
                'withdraw_fee' => $pay_modules->sum('withdraw_fee'),
                'fin_id' => $fin_id,
                'acct_num' => $request->user()->acct_num,
                'acct_name' => $request->user()->acct_name,
                'acct_bank_name' => $request->user()->acct_bank_name,
                'acct_bank_code' => $request->user()->acct_bank_code,
            ];
            $url = $this->base_noti_url.'/single-deposit';
            $res = post($url, $params);
            if($res['code'] == 201)
                return $this->response($res ? 1 : 990);    
            else
                return $this->extendResponse(2000, $res['body']['result_msg']);
        }
        else
            return $this->extendResponse(1000, "활성화된 실시간 모듈을 찾을 수 없습니다.");
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->collect_withdraws->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function update(CollectWithdrawRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->collect_withdraws->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $res = $this->collect_withdraws->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
