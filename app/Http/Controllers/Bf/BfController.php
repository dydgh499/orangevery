<?php

namespace App\Http\Controllers\Bf;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuickView\QuickViewController;
use App\Http\Controllers\Manager\Settle\CollectWithdrawController;
use App\Http\Controllers\Log\CollectWithdrawHistoryController;
use App\Http\Controllers\Manager\Transaction\TransactionController;
use App\Http\Controllers\Log\RealtimeSendHistoryController;

use App\Models\Merchandise;
use App\Models\Merchandise\PaymentModule;
use App\Models\Transaction;
use App\Models\CollectWithdraw;
use App\Models\Log\RealtimeSendHistory;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\LoginRequest;
use App\Http\Requests\Manager\Settle\CollectWithdrawRequest;
use App\Http\Requests\Manager\Transaction\HandPayRequest;

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group BF Mobile API
 *
 * BF Mobile과 PAYVERY간 API 입니다.
 */
class BfController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    /**
     * 로그인
     * 
     * @unauthenticated
     * 
     * @responseFile 200 storage/bf/login.json
     * @responseField access_token string Bearer 토큰 값
     * @responseField user object 유저정보
     */
    public function login(LoginRequest $request)
    {
        if($request->ip() !== '139.150.83.168')
            return $this->response(951);
        else if($request->brand_id == 12 || $request->brand_id == 14 || $request->brand_id == 30)
        {
            if(env('APP_ENV') === 'production' && $_SERVER['SERVER_ADDR'] === '211.45.163.74')
                return $this->extendResponse(1000, '서비스 점검중입니다.');
            else
            {
                $result = Login::isSafeLogin(new Merchandise(), $request);    // check merchandise
                if($result['result'] === 0)
                {
                    $data = $result['user']->loginAPI(10);
                    $data['user'] = [
                        'id' => $data['user']->id,
                        'user_name' => $data['user']->user_name,
                        'mcht_name' => $data['user']->mcht_name,
                        'level' => 10,
                    ];
                    return $this->response(0, $data);
                }
                else if($result['result'] === -1)
                    return $this->extendResponse(1000, __('auth.not_found_obj'));
                else
                    return $this->extendResponse($result['result'], $result['msg'], $result['data']);    
            }            
        }
        else
            return $this->response(951);
    }

    
    /**
     * 결제모듈정보 조회
     *
     * 결제모듈정보를 불러옵니다.<br>한도 및 수기결제에 필요한 데이터들을 조회합니다.
     * @responseFile 200 storage/bf/payModules.json
     * @responseField id integer 결제모듈 고유번호
     * @responseField module_type integer 모듈 타입(0=장비, 1=수기, 2=인증, 3=간편)
     * @responseField settle_fee integer 입금 수수료
     * @responseField is_old_auth integer 비인증, 구인증 여부(비인증=0, 구인증=1)
     * @responseField installment string 할부한도(0~12)
     * @responseField pay_year_limit integer 연결제한도(만 단위)
     * @responseField pay_month_limit integer 월결제한도(만 단위)
     * @responseField pay_single_limit integer 일결제한도(만 단위)
     * @responseField pay_year_amount integer 연결제 금액
     * @responseField pay_month_amount integer 월결제 금액
     * @responseField pay_day_amount integer 일결제 금액
     * @responseField pay_able_amount integer 결제 가능금액(연,월,일,결제한도가 지정되지 않은 경우 null로 반환합니다.)
     * @responseField pay_window_secure_level integer 결제창 보안등급
     * @responseField use_realtime_deposit integer 실시간 이체 사용여부(미사용=0, 사용=1)
     * @responseField fin_trx_delay integer 실시간 이체 딜레이(모아서 출금=-1, 즉시입금=0, 15분=15, 30분=30, 45분=45, 60분=60), 모아서 출금 외 모두 즉시입금
     */
    public function payModules(Request $request)
    {
        $pay_modules = PaymentModule::where('mcht_id', $request->user()->id)
            ->where('is_delete', false)
            ->where('module_type', 1)
            ->with(['payLimitAmount'])
            ->get([
                'id',
                'is_old_auth',
                'module_type',
                'settle_fee',
                'installment',
                'pay_year_limit',
                'pay_month_limit',
                'pay_day_limit',
                'pay_single_limit',
                'terminal_id',
                'pay_window_secure_level as show_pay_view',
                'use_realtime_deposit',
                'fin_trx_delay',
            ]);
        
        foreach($pay_modules as $pay_module)
        {
            if(count($pay_module->payLimitAmount))
            {
                $pay_module->pay_year_amount = (int)$pay_module->payLimitAmount[0]->pay_year_amount;
                $pay_module->pay_month_amount = (int)$pay_module->payLimitAmount[0]->pay_month_amount;
                $pay_module->pay_day_amount = (int)$pay_module->payLimitAmount[0]->pay_day_amount;                    
            }
            else
            {
                $pay_module->pay_year_amount = 0;
                $pay_module->pay_month_amount = 0;
                $pay_module->pay_day_amount = 0;
            }

            $pay_module->makeHidden(['payLimitAmount']);
            $pay_able_amounts = [];
            // 연한도 검증
            if($pay_module->pay_year_limit)
                $pay_able_amounts[] = ($pay_module->pay_year_limit * 10000) - $pay_module->pay_year_amount;
            // 월한도 검증
            if($pay_module->pay_month_limit)
                $pay_able_amounts[] = ($pay_module->pay_month_limit * 10000) - $pay_module->pay_month_amount;
            // 일한도 검증
            if($pay_module->pay_day_limit)
                $pay_able_amounts[] = ($pay_module->pay_day_limit * 10000) - $pay_module->pay_day_amount;

            if(count($pay_able_amounts) > 0)
                $pay_module->pay_able_amount = min($pay_able_amounts);
            else
                $pay_module->pay_able_amount = null;
        }
        return $this->response(0, $pay_modules);
    }

    /**
     * 출금가능금액 조회
     *
     * 출금가능한금액을 조회합니다.<br>즉시 출금 결제모듈의 매출은 포함되지 않습니다.
     * @responseFile 200 storage/bf/withdrawsBalance.json
     * @responseField profit integer 출금가능한도
     */
    public function withdrawsBalance(Request $request)
    {
        $inst = new CollectWithdrawController(new CollectWithdraw);
        return $inst->withdrawAbleAmount($request);
    }

    /**
     * 출금요청
     *
     * 출금가능한금액을 조회합니다.
     * @responseFile 201 storage/bf/withdrawsStore.json
     * @responseField id integer 출금요청 고유번호
     */
    public function withdrawsStore(CollectWithdrawRequest $request)
    {
        $inst = new CollectWithdrawController(new CollectWithdraw);
        $json = $inst->_withdrawAbleAmount($request, $request->user()->id);
        if($json['profit'] >= $request->withdraw_amount)
            return $inst->collectDeposit($request);
        else
            return $this->response(1002);
    }

    /**
     * 수기결제
     *
     * 수기결제 API 입니다.
     * @responseFile 201 storage/bf/handPay.json
     * @responseField mid string 가맹점 MID
     * @responseField tid string 단말기 TID
     * @responseField amount integer 거래금액
     * @responseField ord_num string 가맹점 주문번호
     * @responseField appr_num string 승인번호
     * @responseField item_name string 상품명
     * @responseField trx_id string 거래번호
     * @responseField acquirer string 매입사명
     * @responseField issuer string 발급사명
     * @responseField card_num string 카드번호
     * @responseField installment string 할부기간
     * @responseField method string 결제방식
     * @responseField trx_dttm string 거래시간(Y-m-d H:i:s)
     * @responseField is_cancel integer 취소여부
     */
    public function handPay(HandPayRequest $request)
    {        
        $inst = new TransactionController(new Transaction);
        return $inst->handPay($request);
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
     * @responseFile 201 storage/bf/transactionIndex.json
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
     * @responseField content.*.fin_trx_delay integer 이체 딜레이(분)
     * @responseField content.*.mcht_settle_fee integer 가맹점 입금 수수료
     * @responseField content.*.total_trx_amount integer 가맹점 총 거래 수수료(입금 수수료 + 거래 수수료)
     * @responseField content.*.hold_amount integer 가맹점 유보금 수수료
     */
    
    public function transactionIndex(IndexRequest $request)
    {
        request()->merge([
            'level' => 10,
            'use_realtime_deposit' => 0,
        ]);
        $cols = [
            'transactions.id', 'transactions.trx_dt', 'transactions.trx_tm', 'transactions.cxl_dt', 'transactions.cxl_tm',
            'transactions.sales5_id','transactions.sales4_id','transactions.sales3_id', 'transactions.sales2_id','transactions.sales1_id',
            'transactions.sales5_fee','transactions.sales4_fee','transactions.sales3_fee', 'transactions.sales2_fee','transactions.sales1_fee',
            'transactions.ps_fee','transactions.mcht_fee','transactions.hold_fee','transactions.mcht_settle_fee','transactions.is_cancel',
            'transactions.amount','transactions.module_type','transactions.ord_num','transactions.mid','transactions.tid',
            'transactions.trx_id','transactions.ori_trx_id','transactions.card_num','transactions.issuer','transactions.acquirer',
            'transactions.appr_num','transactions.installment','transactions.buyer_name','transactions.buyer_phone','transactions.item_name',
            'payment_modules.note', 'payment_modules.cxl_type', 'payment_modules.fin_trx_delay',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"), DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
            DB::raw('mcht_settle_amount AS profit'),
        ];
        $query  = TransactionFilter::common($request);
        $data   = $this->getIndexData($request, $query, 'transactions.id', $cols, 'transactions.trx_at', false);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }

    /**
     * 실시간 이체내역 조회
     *
     * 로그인한 가맹점의 실시간 이체내역을 조회합니다.
     *
     * @queryParam page integer required 조회 페이지 Example: 1
     * @queryParam page_size integer required 조회 사이즈 Example: 20
     * @queryParam s_dt string 검색 시작일 Example: 2023-11-01
     * @queryParam e_dt string 검색 종료일 Example: 2023-11-30
     * @queryParam search string 검색어(승인번호, 계좌번호)
     * @responseFile 201 storage/bf/realtimeHistoryIndex.json
     * @responseField page string 조회 페이지
     * @responseField page_size string 조회 사이즈
     * @responseField total string 총 개수
     * @responseField content object[] 결과
     * @responseField content.*.result_code string 결과코드(0000=정상)
     * @responseField content.*.request_type integer 요청타입(6170=이체조회, 6140=이체요청)<br>request_type:6170, result_code:0000 인 것만 성공 이체건으로 인식합니다.
     * @responseField content.*.message string 결과 메세지
     * @responseField content.*.acct_num string 이체 계좌번호
     * @responseField content.*.acct_bank_name string 이체 은행명
     * @responseField content.*.acct_bank_code string 이체 은행코드
     * @responseField content.*.trans_seq_num string 이체번호(고유 값 아님)
     */
    public function realtimeHistoryIndex(IndexRequest $request)
    {
        $cols = [
            'merchandises.mcht_name',
            'transactions.appr_num',
            'transactions.trx_id',
            'realtime_send_histories.*',
        ];
        $inst = new RealtimeSendHistoryController(new RealtimeSendHistory);
        $query = $inst->commonSelect($request)
            ->where('transactions.mcht_id', $request->user()->id);

        $data = $this->getIndexData($request, $query, 'realtime_send_histories.id', $cols, 'realtime_send_histories.created_at');
        return $this->response(0, $data);
    }

    /**
     * 가맹점 모아서 출금내역 조회
     *
     * 로그인한 가맹점의 모아서 출금내역을 조회합니다.
     * @responseFile 201 storage/bf/selfWithdrawIndex.json
     * @responseField content object[] 결과
     * @responseField content.*.withdraw_amount integer 출금 금액
     * @responseField content.*.withdraw_fee integer 출금 수수료
     * @responseField content.*.created_at string 출금신청시간
     * @responseField content.*.acct_num string 계좌번호
     * @responseField content.*.acct_name string 예금주명
     * @responseField content.*.acct_bank_name string 은행명
     * @responseField content.*.acct_bank_code string 은행코드
     * @responseField content.*.result_code string 결과코드
     * @responseField content.*.message string 결과 메세지
     */
    public function selfWithdrawIndex(IndexRequest $request)
    {
        $cols = [
            'collect_withdraws.withdraw_amount',
            'collect_withdraws.withdraw_fee',
            'collect_withdraws.created_at',
            'collect_withdraws.acct_num',
            'collect_withdraws.acct_name',
            'collect_withdraws.acct_bank_name',
            'collect_withdraws.acct_bank_code',
            'collect_withdraws.result_code',
            'collect_withdraws.message',
        ];
        $inst = new CollectWithdrawHistoryController(new CollectWithdraw);
        $query = $inst->commonSelect($request)
            ->where('merchandises.id', $request->user()->id);

        $data = $this->getIndexData($request, $query, 'collect_withdraws.id', $cols, 'collect_withdraws.created_at');
        return $this->response(0, $data);
    }

    /*
    * 매출이 발생했는지 확인
    */
    public function occuerredSale(Request $request)
    {
        $exist = Transaction::where('mcht_id', $request->mcht_id)
            ->where('pmod_id', $request->pmod_id)
            ->exists();
        return $this->response(0, ['exist'=>$exist]);
    }
}
