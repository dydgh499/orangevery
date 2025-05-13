<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;

use App\Models\BankAccount;
use App\Models\Service\FinanceVan;
use App\Models\Service\CMSTransactionBook;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkWithdrawBookRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Withdraw-Batch-Book-Updater API
 *
 * 출금 예약 요청 일괄 업데이트 group 입니다.
 */
class BatchUpdateWithdrawBookController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cms_transaction_books, $target;

    public function __construct(CMSTransactionBook $cms_transaction_books)
    {
        $this->cms_transaction_books = $cms_transaction_books;
        $this->target = '출금예약요청';
    }

    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function register(BulkWithdrawBookRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        if(count($datas) > 1000)
            return $this->extendResponse(1000, '출금요청은 한번에 최대 1000개까지 등록할 수 있습니다.');

        $withdraw = $datas->map(function ($data) use($current, $brand_id) {
            $data['brand_id']   = $brand_id;
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        
        $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->cms_transaction_books, $withdraw, 'account_name', $current, $brand_id);
        return $this->response(1, $ids);
    }

    
    /**
     * 계좌 출금 예약 테스트
     */
    public function withdrawTest(BulkWithdrawBookRequest $request)
    {
        
    $current = date('Y-m-d H:i:s');
    $brand_id = $request->user()->brand_id;
    $datas = $request->data();

    if(!$request->user()->tokenCan(35)) {
        return $this->response(951);
    }

    $results = [];
    $success_count = 0;

    foreach($datas as $data) 
    {
        // [추가] $result 초기화
        $result = [
            'deposit_acct_num' => $data['deposit_acct_num'] ?? null,
            'result_cd' => 100, // 기본 성공 코드
            'result_msg' => ''
        ];

        // 1. 계좌번호 존재 여부 확인
        $bankAccount = BankAccount::where('acct_num', $data['deposit_acct_num'])
            ->where('brand_id', $brand_id)
            ->first();

        if(!$bankAccount) {
            $result['result_cd'] = 952;
            $result['result_msg'] = '존재하지 않는 계좌번호';
            $results[] = $result;
            continue;
        }

        // 2. 금융정보 유효성 검사
        $finance = FinanceVan::where('id', $data['fin_id'])
            ->where('brand_id', $brand_id)
            ->first();

        if(!$finance) {
            $result['result_cd'] = 953;
            $result['result_msg'] = '유효하지 않은 금융정보';
            $results[] = $result;
            continue;
        }

        // 3. 출금 요청 파라미터 구성
        $params = [
            'fin_id' => $data['fin_id'],
            'amount' => $data['withdraw_amount'],
            'note' => $data['note'] ?? '',
            'withdraw_book_time' => $data['withdraw_book_time'] ?? null,
        ];
        $params = array_merge($bankAccount->toArray(), $params);
        unset($params['checked']);

        // 4. 등록 처리 (임시 코드)
        $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->cms_transaction_books, [$params], 'fin_id',$current, $brand_id);

        // [추가] 성공 결과 업데이트
        $result['result_cd'] = 100;
        $result['result_msg'] = '성공';
        $results[] = $result;
        $success_count++;
    }

    // 5. 최종 응답
    $message = "총 ".count($datas)."건 중 {$success_count}건 성공";
    return $this->extendResponse(1, $message, [
        'total' => count($datas),
        'success' => $success_count,
        'failed' => count($datas) - $success_count,
        'details' => $results
    ]);

        // 6. 외부 API 호출
        /*
        try {
            $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/operate-withdraw', $params
            );
            
            $result['result_cd'] = $res['body']['result_cd'] ?? 9999;
            $result['result_msg'] = $res['body']['result_msg'] ?? 'API 오류 발생';
            
            if($result['result_cd'] === 100) $success_count++;
            
        } catch (\Exception $e) {
            $result['result_cd'] = 500;
            $result['result_msg'] = '서버 오류: '.$e->getMessage();
        }
        */
    }
}
