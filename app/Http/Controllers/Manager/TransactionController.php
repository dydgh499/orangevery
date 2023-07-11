<?php

namespace App\Http\Controllers\Manager;

use App\Models\Transaction;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Database\QueryException;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $transactions;

    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
    }

    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query  = $this->transactions
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where(function ($query) use ($search) {
                return $query->where('mid', 'like', "%$search%")
                    ->orWhere('tid', 'like', "%$search%")
                    ->orWhere('trx_id', 'like', "%$search%")
                    ->orWhere('appr_num', 'like', "%$search%");
            });
            
        if($request->has('s_dt') && $request->has('e_dt'))
        {
            $query = $query->where(function($query) use($request) {
                $query->where(function($query) use($request) {
                    $query->where('is_cancel', false)
                        ->where('trx_dt', '>=', $request->s_dt)
                        ->where('trx_dt', '<=', $request->e_dt);
                })->orWhere(function($query) use($request) {
                    $query->where('is_cancel', true)
                        ->where('cxl_dt', '>=', $request->s_dt)
                        ->where('cxl_dt', '<=', $request->e_dt);
                });
            });
            $request->query->remove('s_dt');
            $request->query->remove('e_dt');
        }
        $query = globalPGFilter($query, $request);
        $query = globalSalesFilter($query, $request);
        $query = globalAuthFilter($query, $request);

        if($request->has('mcht_settle_id'))
            $query = $query->where('mcht_settle_id', $request->mcht_settle_id);

        for ($i=0; $i < 6; $i++) { 
            $col = 'sales'.$i.'_settle_id';
            if($request->has($col))
                $query = $query->where($col, $request->input($col));
        }
        return $query;
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 99999999,
        ]);
        $query  = $this->commonSelect($request);
        $data   = $this->getIndexData($request, $query);
        $chart  = getDefaultTransChartFormat($data['content']);
        return $this->response(0, $chart);
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->commonSelect($request);
        $query = $query->with(['mcht']);
        $data = $this->getIndexData($request, $query, [], 'trx_dttm');

        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);

        foreach($data['content'] as $content) 
        {
            $content->append(['total_trx_amount']);
        }
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TransactionRequest $request)
    {
        $data = $request->data();
        $res = $this->transactions->create($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $data = $this->transactions->where('id', $id)->first();
        if($data)
        {
            $data->ps_fee = number_format($data->ps_fee * 100, 3);
            $data->mcht_fee = number_format($data->mcht_fee * 100, 3);
            $data->hold_fee = number_format($data->hold_fee * 100, 3);
            $data->sales5_fee = number_format($data->sales5_fee * 100, 3);
            $data->sales4_fee = number_format($data->sales4_fee * 100, 3);
            $data->sales3_fee = number_format($data->sales3_fee * 100, 3);
            $data->sales2_fee = number_format($data->sales2_fee * 100, 3);
            $data->sales1_fee = number_format($data->sales1_fee * 100, 3);
            $data->sales0_fee = number_format($data->sales0_fee * 100, 3);
        }
        else
            return $this->response(1000);
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TransactionRequest $request, $id)
    {
        $data = $request->data();
        $res = $this->transactions->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->delete($this->transactions->where('id', $id));
            return $this->response($res);
        }
        else
            return $this->response(951);
    }

    /**
     * 취소매출 생성
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(TransactionRequest $request)
    {
        $data = $request->data();
        // TransactionRequest 에서 100을 먼저 나눠서 가져오기 떄문에 다시가져옴
        $data['ps_fee']  = $request->input('ps_fee', 0);
        $data['hold_fee']  = $request->input('hold_fee', 0);
        $data['mcht_fee']    = $request->input('mcht_fee', 0);
        $data['sales0_fee'] = $request->input('sales0_fee', 0);
        $data['sales1_fee'] = $request->input('sales1_fee', 0);
        $data['sales2_fee'] = $request->input('sales2_fee', 0);
        $data['sales3_fee'] = $request->input('sales3_fee', 0);
        $data['sales4_fee'] = $request->input('sales4_fee', 0);
        $data['sales5_fee'] = $request->input('sales5_fee', 0);
        try 
        {
            $res = $this->transactions->create($data);
            return $this->response(1);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.';
                
            return $this->extendResponse(990, $msg);
        }
    }

    public function handPay(Request $request)
    {
        $getYYMM = function($mmyy) {
            if(mb_strlen($mmyy, 'utf-8') == 4)
            {
                $first 	= substr($mmyy, 0, 2);
                $sec 	= substr($mmyy, 2, 2);
                return $sec.$first;
            }
            else
                return '';
        };

        $data = $request->all();
        $data['yymm'] = $getYYMM($data['yymm']);
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/hand';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
            return $this->response(1, $res['body']);
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);
      
    }

    public function payCancel(Request $request)
    {
        $data = $request->all();
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/pay/cancel';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
            return $this->response(1, $res['body']);
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);

    }
}
