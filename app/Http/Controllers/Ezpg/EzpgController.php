<?php

namespace App\Http\Controllers\Ezpg;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\TransactionController;

use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Models\Transaction;

use App\Http\Requests\Manager\IndexRequest;
use App\Http\Requests\Manager\LoginRequest;

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

    /**
     * 로그인
     * 
     * @unauthenticated
     * 
     * @bodyParam user_name string 가맹점 아이디
     * @bodyParam user_pw string 가맹점 패스워드
     * @responseFile 200 storage/buddyPay/login.json
     * @responseField access_token string Bearer 토큰 값
     * @responseField user object 유저정보
     */
    public function login(Request $request)
    {
        $validated = $request->validate(['user_name'=>'required|string', 'user_pw'=>'required|string']);
        $request = $request->merge(['brand_id' => 19]);

        $inst = new AuthController();
        $result = $inst->__signIn(new Merchandise(), $request);  // check Merchandise
        if($result['result'] == 1)
        {
            $data = $result['user']->loginInfo(10);
            $data['user'] = [
                'id' => $data['user']->id,
                'user_name' => $data['user']->user_name,
                'level' => 10,
            ];
            return $inst->response(0, $data);
        }
        else
            return $this->extendResponse(1000, __('auth.not_found_obj'));
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
     */
    public function transactionIndex(IndexRequest $request)
    {
        $request->merge([
            'level' => 10,
            'use_search_date_detail' => 0,
            'use_realtime_deposit'   => 0,
            'use_cancel_deposit'     => 0,
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
        $query  = $inst->commonSelect($request);
        $data   = $inst->getTransactionData($request, $query);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }
}
