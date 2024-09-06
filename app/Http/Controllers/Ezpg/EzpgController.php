<?php

namespace App\Http\Controllers\Ezpg;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\Transaction\TransactionController;

use App\Models\Merchandise;
use App\Models\Merchandise\PaymentModule;
use App\Models\Transaction;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\LoginRequest;

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group EZPG API
 *
 * EZPG와 Smart Data간 API 입니다.
 */
class EzpgController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    public function getMerchant($request)
    {
        $pay_key = str_replace("Bearer ", "", request()->header('Authorization'));
        return Merchandise::join('payment_modules', 'merchandises.id', '=', 'payment_modules.mcht_id')
            ->where('merchandises.id', $request->user()->id)
            ->where('payment_modules.pay_key', $pay_key)
            ->where('payment_modules.is_delete', false)
            ->first(['merchandises.id']);
    }

    /**
     * 로그인
     * 
     * @unauthenticated
     * 
     * @bodyParam user_name string required 가맹점 아이디
     * @bodyParam user_pw string required 가맹점 패스워드
     * @responseFile 200 storage/buddyPay/login.json
     * @responseField access_token string Bearer 토큰 값
     * @responseField user object 유저정보
     */
    public function login(Request $request)
    {
        $validated = $request->validate(['user_name'=>'required|string', 'user_pw'=>'required|string']);
        $request = $request->merge(['brand_id' => 4]);

        $result = Login::isSafeLogin(new Merchandise(), $request);    // check merchandise
        if($result['result'] === 0)
        {
            $data = $result['user']->loginAPI(10);
            $data['user'] = [
                'id' => $data['user']->id,
                'user_name' => $data['user']->user_name,
                'level' => 10,
            ];
            return $this->response(0, $data);
        }
        else if($result['result'] === -1)
            return $this->extendResponse(1000, __('auth.not_found_obj'));
        else
            return $this->extendResponse($result['result'], $result['msg'], $result['data']);
    }

    /**
     * 결제내역 조회
     *
     * 로그인한 가맹점의 결제내역을 조회합니다.
     * 
     * @queryParam page integer required 조회 페이지 Example: 1
     * @queryParam page_size integer required 조회 사이즈 Example: 20
     * @queryParam s_dt string 검색 시작일 Example: 2023-11-01
     * @queryParam e_dt string 검색 종료일 Example: 2023-11-30
     * @queryParam search string 검색어(MID, TID, 거래번호, 승인번호, 발급사, 매입사, 결제모듈 별칭)
     * @responseFile 201 storage/buddyPay/transactionIndex.json
     * @responseField page string 조회 페이지
     * @responseField page_size string 조회 사이즈
     * @responseField total string 총 개수
     * @responseField content object[] 결과
     * @responseField content.*.ps_fee integer PG사 구간 수수료(%)
     * @responseField content.*.sales5_fee integer 총판 수수료(%)
     * @responseField content.*.sales4_fee integer 지사 수수료(%)
     * @responseField content.*.sales3_fee integer 대리점1 수수료(%)
     * @responseField content.*.sales3_fee integer 대리점2 수수료(%)
     * @responseField content.*.sales2_fee integer 대리점3 수수료(%)
     * @responseField content.*.sales1_fee integer 대리점4 수수료(%)
     * @responseField content.*.mcht_fee integer 가맹점 수수료(%)
     * @responseField content.*.hold_fee integer 유보금 수수료(%)
     * @responseField content.*.is_cancel integer 취소여부(0=승인, 1=취소)
     * @responseField content.*.cxl_type integer 취소 타입취소타입(0=취소금지, 1=이체시간 -5분, 2=당일허용)
     * @responseField content.*.amount integer 거래금액
     * @responseField content.*.profit integer 가맹점 정산금액
     * @responseField content.*.trx_amount integer 가맹점 거래 수수료
     * @responseField content.*.mcht_settle_fee integer 가맹점 입금 수수료
     * @responseField content.*.total_trx_amount integer 가맹점 총 거래 수수료(입금 수수료 + 거래 수수료)
     * @responseField content.*.hold_amount integer 가맹점 유보금 수수료
     * @responseField content.*.trx_dt string 승인일자
     * @responseField content.*.trx_tm string 승인시간
     * @responseField content.*.cxl_dt string 취소일자(취소일 경우)
     * @responseField content.*.cxl_tm string 취소시간(취소일 경우)
     * @responseField content.*.cxl_seq integer 취소회차(취소일 경우)
     * @responseField content.*.ori_trx_id string 원거래에 사용된 transaction ID (취소일 경우)
     * @responseField content.*.ord_num string 거래에 사용된 order number
     * @responseField content.*.trx_id string 거래에 사용된 transaction ID
     * @responseField content.*.mid string 거래에 사용된 PG사 MID
     * @responseField content.*.tid string 거래에 사용된 terminal ID
     * @responseField content.*.card_num string 마스킹된 카드번호
     * @responseField content.*.issuer string 발급사
     * @responseField content.*.acquirer string 매입사
     * @responseField content.*.installment integer 할부개월(0~12)
     * @responseField content.*.appr_num string 승인번호
     */
    public function transactionIndex(IndexRequest $request)
    {
        request()->merge([
            'level' => 10,
            'use_realtime_deposit' => 0,
        ]);
        $inst   = new TransactionController(new Transaction);
        $inst->cols = [
            'transactions.id', 'transactions.trx_dt', 'transactions.trx_tm', 'transactions.cxl_dt', 'transactions.cxl_tm',
            'transactions.sales5_id','transactions.sales4_id','transactions.sales3_id', 'transactions.sales2_id','transactions.sales1_id',
            'transactions.sales5_fee','transactions.sales4_fee','transactions.sales3_fee', 'transactions.sales2_fee','transactions.sales1_fee',
            'transactions.ps_fee','transactions.mcht_fee','transactions.hold_fee','transactions.mcht_settle_fee','transactions.is_cancel',
            'transactions.amount','transactions.module_type','transactions.ord_num','transactions.mid','transactions.tid',
            'transactions.trx_id','transactions.ori_trx_id','transactions.card_num','transactions.issuer','transactions.acquirer',
            'transactions.appr_num','transactions.installment','transactions.buyer_name','transactions.buyer_phone','transactions.item_name',
            'payment_modules.note', 'payment_modules.cxl_type',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"), DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
        $inst->setTransactionData(10);
        $query = TransactionFilter::common($request);
        $data           = $inst->getIndexData($request, $query, 'transactions.id', $inst->cols, 'transactions.trx_at', false);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }

    /**
     * 실시간 거래대사
     *
     * 특정 기간의 결제내역을 조회합니다.
     * 
     * @queryParam page integer required 조회 페이지 Example: 1
     * @queryParam page_size integer required 조회 사이즈 Example: 20
     * @queryParam s_at string required 검색 시작일 Example: 2023-11-01 00:00:00
     * @queryParam e_at string required 검색 종료일 Example: 2023-11-30 23:59:59
     * @responseFile 201 storage/reconciliations/index.json
     * @responseField page integer 조회 페이지
     * @responseField page_size integer 조회 사이즈
     * @responseField total integer 총 개수
     * @responseField content object[] 결과
     * @responseField content.*.is_cancel integer 취소여부(0=승인, 1=취소)
     * @responseField content.*.trx_dt string 승인일자
     * @responseField content.*.trx_tm string 승인시간
     * @responseField content.*.cxl_dt string 취소일자(취소일 경우)
     * @responseField content.*.cxl_tm string 취소시간(취소일 경우)
     * @responseField content.*.cxl_seq integer 취소회차(취소일 경우)
     * @responseField content.*.ori_trx_id string 원거래에 사용된 transaction ID (취소일 경우)
     * @responseField content.*.amount integer 거래금액
     * @responseField content.*.ord_num string 거래에 사용된 order number
     * @responseField content.*.trx_id string 거래에 사용된 transaction ID
     * @responseField content.*.mid string 거래에 사용된 PG사 MID
     * @responseField content.*.tid string 거래에 사용된 terminal ID
     * @responseField content.*.card_num string 마스킹된 카드번호
     * @responseField content.*.issuer string 발급사
     * @responseField content.*.acquirer string 매입사
     * @responseField content.*.installment integer 할부개월(0~12)
     * @responseField content.*.appr_num string 승인번호
     * 
     */
    public function reconciliation(Request $request)
    {
        $validated = $request->validate([
            'page'      => 'required|integer',
            'page_size' => 'required|integer',
            's_at'  => 'required|string',
            'e_at'  => 'required|string',
        ]);
        
        $carbon_s_at = Carbon::createFromFormat('Y-m-d H:i:s', $request->s_at);
        $carbon_e_at = Carbon::createFromFormat('Y-m-d H:i:s', $request->e_at);
        if($request->page_size > 3000)
            return $this->extendResponse(409, '페이지 사이즈는 최대 3000개까지 허용합니다.');
        else if($carbon_s_at->diffInDays($carbon_e_at, false) > 7)
            return $this->extendResponse(409, '조회 날짜는 최대 7일까지 허용합니다.');
        else
        {
            $cols = [
                'transactions.is_cancel', 
                'transactions.trx_dt', 'transactions.trx_tm', 'transactions.cxl_dt', 'transactions.cxl_tm', 'transactions.cxl_seq', 'transactions.ori_trx_id',
                'transactions.amount', 'transactions.ord_num', 'transactions.trx_id', 'transactions.mid', 'transactions.tid',
                'transactions.card_num', 'transactions.issuer', 'transactions.acquirer', 'transactions.installment', 'transactions.appr_num',
                DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"), DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
            ];

            $query = TransactionFilter::common($request);
            $data = $this->getIndexData($request, $query, 'transactions.id', $cols, 'transactions.trx_at');
            foreach($data['content'] as $item)
            {
                unset($item['trx_dttm']);
                unset($item['cxl_dttm']);
                unset($item['trx_amount']);
                unset($item['hold_amount']);
                unset($item['total_trx_amount']);
            }
            return $this->response(0, $data);
        }
    }

    /**
     * 일별 요약 거래대사
     *
     * 특정 일의 결제내역을 조회합니다.
     * 
     * @queryParam t_dt string required 조회일 Example: 2023-11-01
     * @responseFile 201 storage/reconciliations/summary.json
     * @responseField appr_amount integer 승인 금액 합계
     * @responseField appr_count integer 승인 건수 합계
     * @responseField cxl_amount integer 취소 금액 합계
     * @responseField cxl_count integer 취소 건수 합계
     * @responseField sales_amount integer 매출(승인+취소) 합계
     * @responseField total_count integer 총 건수
     */
    public function summary(Request $request)
    {
        $validated = $request->validate(['t_dt'  => 'required|string']);
        $cols = [
            'mcht_id',            
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS appr_amount"),
            DB::raw("SUM(is_cancel = 0) AS appr_count"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS cxl_amount"),
            DB::raw("SUM(is_cancel = 1) AS cxl_count"),
            DB::raw("SUM(amount) AS sales_amount"),
            DB::raw("COUNT(*) AS total_count"),
        ];
        
        $chart = TransactionFilter::common($request)
            ->groupBy('transactions.mcht_id')
            ->first($cols);

        if($chart)
        {
            $chart = json_decode(json_encode($chart), true);                
            unset($chart['trx_dttm']);
            unset($chart['cxl_dttm']);
            unset($chart['trx_amount']);
            unset($chart['hold_amount']);
            unset($chart['total_trx_amount']);

            $chart['appr_amount'] = (int)$chart['appr_amount'];
            $chart['appr_count'] = (int)$chart['appr_count'];
            $chart['cxl_amount'] = (int)$chart['cxl_amount'];
            $chart['cxl_count'] = (int)$chart['cxl_count'];


                
            $chart['sales_amount'] = (int)$chart['sales_amount'];
            $chart['total_count'] = (int)$chart['total_count'];
        }

        return $this->response(0, $chart);
    }
}
