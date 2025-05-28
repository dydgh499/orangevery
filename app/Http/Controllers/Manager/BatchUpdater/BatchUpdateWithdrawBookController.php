<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;

use App\Models\BankAccount;
use App\Models\Service\FinanceVan;
use App\Models\Service\CMSTransactionBooks;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkWithdrawBookRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Manager\Service\CMSTransactionBookController;
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

    public function __construct(CMSTransactionBooks $cms_transaction_books)
    {
        $this->cms_transaction_books = $cms_transaction_books;
        $this->target = '출금예약요청';
    }

    protected static function getTrxNum($brand_id)
    {
        $withdraw_count  = CMSTransactionBooks::where('brand_id', $brand_id)
                ->where('is_withdraw', 1)
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->lockForUpdate() // Lock 추가
                ->count();

        $code = 3;
        $head = date("d").$brand_id;
        return self::getTrxNumFormat($code, $head, $withdraw_count);
    }

    /**
     * trans_seq_num 포맷팅 함수 추가
     */
    protected static function getTrxNumFormat($code, $head, $withdraw_count)
    {
        $body = str_pad($withdraw_count+1, 11 - strlen($head), "0", STR_PAD_LEFT);
        return (int)$code.$head.$body;
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

    private function transactionBookBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        
        if ($apply_mode === 3) {
            $this->wrongTypeAccess();
        } 
        else if ($apply_mode === 1) {
            return $this->cms_transaction_books->whereIn('cms_transaction_books.id', $request->selected_idxs);
        } 
        else {
            $this->wrongTypeAccess();
        }
    }
    

    
    /**
     * 계좌 출금 예약 테스트(등록되지 않은 계좌 있을시 출금 예약 실패)
     */
    public function withdrawNoAccount(BulkWithdrawBookRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();

        if(!$request->user()->tokenCan(35)) {
            return $this->response(951);
        }

        $not_exist_accounts = [];

        // 1. 모든 계좌번호 존재 여부 먼저 확인
        foreach($datas as $data) {
            $bankAccount = BankAccount::where('acct_num', $data['deposit_acct_num'])
                ->where('brand_id', $brand_id)
                ->first();

            if(!$bankAccount) {
                $not_exist_accounts[] = $data['deposit_acct_num'];
            }
        }

        // 2. 하나라도 존재하지 않는 계좌가 있으면 등록 중단 및 반환
        if (!empty($not_exist_accounts)) {
            // 중복 제거
            $not_exist_accounts = array_unique($not_exist_accounts);

            $message = "등록되지 않은 계좌번호가 존재합니다: " . implode(", ", $not_exist_accounts);
            return $this->extendResponse(990, $message, [
                'not_exist_accounts' => $not_exist_accounts,
                'success' => 0,
                'failed' => count($datas)
            ]);
        }

        // 3. 모든 계좌가 존재할 때만 등록 처리
        $results = [];
        $success_count = 0;

        foreach($datas as $data) 
        {
            // 2. 금융정보 유효성 검사
            $finance = FinanceVan::where('id', $data['fin_id'])
                ->where('brand_id', $brand_id)
                ->first();

            if(!$finance) {
                $results[] = [
                    'deposit_acct_num' => $data['deposit_acct_num'] ?? null,
                    'result_cd' => 953,
                    'result_msg' => '유효하지 않은 금융정보'
                ];
                continue;
            }

            // 3. 출금 요청 파라미터 구성
            $bankAccount = BankAccount::where('acct_num', $data['deposit_acct_num'])
                ->where('brand_id', $brand_id)
                ->first();

            $trans_seq_num = self::getTrxNum($brand_id);

            $params = [
                'brand_id' => $brand_id,
                'fin_id' => $data['fin_id'],
                'is_withdraw' => '1',
                'amount' => $data['withdraw_amount'],
                'note' => $data['note'] ?? '',
                'acct_num' => $bankAccount->acct_num,
                'acct_name' => $bankAccount->acct_name,
                'acct_bank_name' => $bankAccount->acct_bank_name,
                'acct_bank_code' => $bankAccount->acct_bank_code,
                'withdraw_book_time' => $data['withdraw_book_time'] ?? null,
                'trans_seq_num' => $trans_seq_num,
                'created_at' => $current,
                'updated_at' => $current,
            ];

            // 4. 등록 처리
            $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->cms_transaction_books, [$params], 'fin_id', $current, $brand_id);

            $results[] = [
                'deposit_acct_num' => $data['deposit_acct_num'] ?? null,
                'result_cd' => 100,
                'result_msg' => '성공'
            ];
            $success_count++;
        }

        // 5. 최종 응답
        $failed_count = count($datas) - $success_count;
        $message = "총 " . count($datas) . "건 중 {$success_count}건 성공";
        return $this->extendResponse(1, $message, [
            'total' => count($datas),
            'success' => $success_count,
            'failed' => $failed_count,
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


    
    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {        
        // 출금예약 내역 쿼리 가져오기
        $query = $this->transactionBookBatch($request);
        // 활동 기록 및 삭제 실행
        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'id');
        return $this->extendResponse($row ? 1 : 990, $row ? $row.'개의 출금예약이 삭제되었습니다.' : '삭제된 출금예약 내역이 존재하지 않습니다.');
    }
}
