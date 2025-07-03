<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Utils\Comm;

use App\Models\BankAccount;
use App\Models\Service\FinanceVan;
use App\Models\Service\CMSTransaction;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkWithdrawBookRequest;

use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Models\Service\CMSTransactionHistories;

/**
 * @group Withdraw-Batch-Book-Updater API
 *
 * 출금 예약 요청 일괄 업데이트 group 입니다.
 */
class BatchUpdateWithdrawBookController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cms_transactions, $cms_transaction_histories;

    public function __construct(CMSTransaction $cms_transactions, CMSTransactionHistories $cms_transaction_histories)
    {
        $this->cms_transaction_histories = $cms_transaction_histories;
        $this->cms_transactions = $cms_transactions;
        $this->target = '이체 예약현황';
    }

    protected static function getTrxNum($brand_id)
    {
        $withdraw_count  = CMSTransaction::where('brand_id', $brand_id)
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->count();

        $head = date("d") . $brand_id;
        $code = 3;

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
     * 계좌 출금 예약 테스트(등록되지 않은 계좌 있을시 출금 예약 실패)
     */
    public function register(BulkWithdrawBookRequest $request)
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
            $bankAccount = BankAccount::where('acct_num', $data['acct_num'])
                ->where('brand_id', $brand_id)
                ->first();

            if(!$bankAccount) {
                $not_exist_accounts[] = $data['acct_num'];
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
                    'acct_num' => $data['acct_num'] ?? null,
                    'result_cd' => 953,
                    'result_msg' => '유효하지 않은 금융정보'
                ];
                continue;
            }

            // 3. 출금 요청 파라미터 구성
            $bankAccount = BankAccount::where('acct_num', $data['acct_num'])
                ->where('brand_id', $brand_id)
                ->first();

            $trans_seq_num = self::getTrxNum($brand_id);

            $params = [
                'brand_id' => $brand_id,
                'fin_id' => $data['fin_id'],
                'amount' => $data['withdraw_amount'],
                'acct_num' => $bankAccount->acct_num,
                'acct_name' => $bankAccount->acct_name,
                'acct_bank_name' => $bankAccount->acct_bank_name,
                'acct_bank_code' => $bankAccount->acct_bank_code,
                'withdraw_book_time' => $data['withdraw_book_time'] ?? null,
                'trans_seq_num' => $trans_seq_num,
                'created_at' => $current,
                'updated_at' => $current,
            ];
            
            // ✅ 예약 이력 기록
            app(ActivityHistoryInterface::class)->batchAdd(
                $this->target,         // 또는 context에 맞는 target 문자열
                $this->cms_transaction_histories,         // 실제 예약 관련된 테이블명 또는 키
                [$params],                                // 예약에 사용된 파라미터
                'fin_id',                                 // 기준 컬럼명
                $current,                                 // 등록 시간
                $brand_id                                 // 브랜드 ID
            );
            
            // 4. 외부 API 호출 대체 - 내부 로직 직접 호출
            try {
                $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/book-withdraw', $params);

                if($res['body']['result_cd'] === '0000')
                {
                    $results[] = [
                        'acct_num' => $data['acct_num'] ?? null,
                        'result_cd' => 100,
                        'result_msg' => '이체 예약 완료',
                    ];
                    $success_count++;
                }else {
                    $results[] = [
                        'acct_num' => $data['acct_num'] ?? null,
                        'result_cd' => $res['body']['result_cd'],
                        'result_msg' => $res['body']['result_msg']
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'acct_num' => $data['acct_num'] ?? null,
                    'result_cd' => 500,
                    'result_msg' => '서버 오류: ' . $e->getMessage()
                ];
            }
        }
        // 6. 최종 응답
        $failed_count = count($datas) - $success_count;
        $message = "총 " . count($datas) . "건 중 {$success_count}건 성공";
        return $this->extendResponse(1, $message, [
            'total' => count($datas),
            'success' => $success_count,
            'failed' => $failed_count,
            'details' => $results
        ]);
    }
}
