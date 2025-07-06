<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateBankAccountController;
use App\Http\Controllers\Utils\Comm;

use App\Models\BankAccount;
use App\Models\Service\FinanceVan;
use App\Models\Service\CMSTransaction;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkWithdrawBookRequest;

/**
 * @group Withdraw-Batch-Book-Updater API
 *
 * 출금 예약 요청 일괄 업데이트 group 입니다.
 */
class BatchUpdateWithdrawBookController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cms_transactions;

    public function __construct(CMSTransaction $cms_transactions)
    {
        $this->cms_transactions = $cms_transactions;
        $this->target = '이체 예약현황';
    }

    /**
     * 계좌 출금 예약 테스트(등록되지 않은 계좌 있을시 출금 예약 실패)
     */
    public function register(BulkWithdrawBookRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $oper_id = $request->user()->id;
        $withdraws = $request->data();

        if(!$request->user()->tokenCan(35)) {
            return $this->response(951);
        }

        $results = [];
        $success_count = 0;

        foreach($withdraws as $withdraw) 
        {
            // 2. 금융정보 유효성 검사
            $finance = FinanceVan::where('id', $withdraw['fin_id'])
                ->where('brand_id', $brand_id)
                ->first();

            if(!$finance) {
                $results[] = [
                    'acct_num' => $withdraw['acct_num'] ?? null,
                    'result_cd' => 953,
                    'result_msg' => '유효하지 않은 금융정보'
                ];
                continue;
            }

            // 3. 출금 요청 파라미터 구성
            $bankAccount = BankAccount::where('acct_num', $withdraw['acct_num'])
                ->where('brand_id', $brand_id)
                ->first();

            $params = [
                'brand_id' => $brand_id,
                'fin_id' => $withdraw['fin_id'],
                'oper_id' => $oper_id,
                'amount' => $withdraw['withdraw_amount'],
                'acct_num' => $bankAccount->acct_num,
                'acct_name' => $bankAccount->acct_name,
                'acct_bank_name' => $bankAccount->acct_bank_name,
                'acct_bank_code' => $bankAccount->acct_bank_code,
                'withdraw_book_time' => $withdraw['withdraw_book_time'],
                'created_at' => $current,
                'updated_at' => $current,
            ];
            
            // 4. 외부 API 호출
            try {
                $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/book-withdraw', $params);
                if($res['code'] === 201)
                {
                    if($res['body']['result_cd'] === '0000')
                    {
                        $results[] = [
                            'acct_num' => $withdraw['acct_num'],
                            'result_cd' => 100,
                            'result_msg' => '이체 예약 완료',
                        ];
                        $success_count++;
                    }else {
                        $results[] = [
                            'acct_num' => $withdraw['acct_num'],
                            'result_cd' => $res['body']['result_cd'],
                            'result_msg' => $res['body']['result_msg']
                        ];
                    }
                }
                else
                {
                    $message = isset($res['body']['result_msg']) ? $res['body']['result_msg'] : '이체 예약 에러';
                }
            } catch (\Exception $e)
            {
                error($withdraw, 'book-withdraw-job-batch'.$e->getMessage()());
                $results[] = [
                    'acct_num' => $withdraw['acct_num'] ?? null,
                    'result_cd' => 500,
                    'result_msg' => '이체 예약 내부 처리 에러 ' . $e->getMessage()
                ];
            }
        }
        // 6. 최종 응답
        $failed_count = count($withdraws) - $success_count;
        $message = "총 " . count($withdraws) . "건 중 {$success_count}건 성공";
        return $this->extendResponse(1, $message, [
            'total' => count($withdraws),
            'success' => $success_count,
            'failed' => $failed_count,
            'details' => $results
        ]);
    }
}
