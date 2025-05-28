<?php

namespace App\Http\Controllers\Manager\Service;


use App\Models\BankAccount;
use App\Models\Service\FinanceVan;
use App\Models\Service\HeadOfficeAccount;
use App\Models\Service\CMSTransactionBook;
use App\Models\Log\SettleHistoryMerchandiseDeposit;
use App\Models\Log\SettleHistorySalesforceDeposit;

use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Utils\Comm;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group CMS Transaciton Book API
 *
 * CMS 거래 예약 API 입니다.
 */
class CMSTransactionBookController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cms_transaction_books;

    public function __construct(CMSTransactionBook $cms_transaction_books)
    {
        $this->cms_transaction_books = $cms_transaction_books;
    }

    /*
    * 목록출력
    */
    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = $this->cms_transaction_books
        ->where('brand_id', $request->user()->brand_id)
        ->where('withdraw_status', 0)
        ->where(function ($query) use ($search) {
            return $query->where('acct_num', 'like', "%$search%")
                ->orWhere('note', 'like', "%$search%");
        });
        if($request->fin_id !== null)
            $query = $query->where('fin_id', $request->fin_id);
        if($request->is_withdraw !== null)
            $query = $query->where('is_withdraw', $request->is_withdraw);
        
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    
    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function delete(int $id)
    {
        $query = new CMSTransactionBook;
        $res = $query->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     */
    public function cancelJobTest(Request $request)
    {
        // 운영자 권한 체크
        //if (Ablilty::isOperator($request)) {
            // id 값 유효성 검사 (필수)
            $validated = $request->validate(['id' => 'required|integer']);
            // 외부 API 호출 (id를 배열 형태로 전달)
            $url = env('NOTI_URL', 'http://localhost:81') . '/api/v2/realtimes/cancel-job-test';
            $res = Comm::post($url, ['id' => [$request->id]]);

            // 외부 API 응답 반환
            return $this->apiResponse($res['body']['result_cd'], $res['body']['result_msg']);
        /* } else
            return $this->response(951); // 권한 없음
            */
    }
}
