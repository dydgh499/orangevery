<?php

namespace App\Http\Controllers\Bf;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuickView\QuickViewController;
use App\Http\Controllers\Manager\CollectWithdrawController;
use App\Http\Controllers\Manager\TransactionController;

use App\Models\Merchandise;
use App\Models\PaymentModule;
use App\Models\Transaction;
use App\Models\CollectWithdraw;

use App\Http\Requests\Manager\LoginRequest;
use App\Http\Requests\Manager\CollectWithdrawRequest;
use App\Http\Requests\Pay\HandPayRequest;

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
        if($request->brand_id == 12 || $request->brand_id == 14)
        {
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
        else
            return $this->response(951);
    }

    
    /**
     * 결제모듈 정보 조회
     *
     * 결제모듈 정보를 불러옵니다.<br>한도 및 수기결제에 필요한 데이터들을 조회합니다.
     * @responseFile 200 storage/bf/payModules.json
     * @responseField id integer 결제모듈 고유번호
     * @responseField is_old_auth integer 비인증, 구인증 여부(비인증=0, 구인증=1)
     * @responseField installment string 할부한도(0~12)
     * @responseField pay_year_limit integer 연결제 한도(만 단위)
     * @responseField pay_month_limit integer 월결제 한도(만 단위)
     * @responseField pay_single_limit integer 일결제 한도(만 단위)
     */
    public function payModules(Request $request)
    {
        $pay_modules = PaymentModule::where('mcht_id', $request->user()->id)
            ->where('module_type', 1)
            ->with(['payLimitAmount'])
            ->get([
                'id',
                'is_old_auth',
                'installment',
                'pay_year_limit',
                'pay_month_limit',
                'pay_day_limit',
                'pay_single_limit',
            ]);
        /*
        -  당일 사용금액 추가
        -  당월 사용금액 추가
        -  결제 가능금액 추가 
        */
        return $this->response(0, $pay_modules);
    }

    /**
     * 출금가능금액 조회
     *
     * 출금가능한금액을 조회합니다.
     * @responseFile 200 storage/bf/withdrawsBalance.json
     * @responseField profit integer 출금가능한도
     */
    public function withdrawsBalance(Request $request)
    {
        $inst = new QuickViewController(new Transaction);
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
        $inst = new QuickViewController(new Transaction);
        $json = $inst->withdrawAbleAmount($request);
        $body = json_decode($json->getContent(), true);
        if($body['profit'] >= $request->withdraw_amount)
        {
            $inst = new CollectWithdrawController(new CollectWithdraw);
            return $inst->store($request);    
        }
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
}
