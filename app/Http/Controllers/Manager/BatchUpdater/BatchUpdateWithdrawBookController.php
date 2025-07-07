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
        $now = now();
        $brandId  = $request->user()->brand_id;
        $operId   = $request->user()->id;
        $rows     = collect($request->data());
        $finances = FinanceVan::where('brand_id', $brandId)
                    ->whereIn('id', $rows->pluck('fin_id'))
                    ->get()->keyBy('id');

        $accounts = BankAccount::where('brand_id', $brandId)
                    ->whereIn('acct_num', $rows->pluck('acct_num'))
                    ->get()->keyBy('acct_num');

        $results  = [];
        $success_count = 0;
    foreach ($rows as $withdraw) {
        // 금융사 유효성 확인
        if (!$finances->has($withdraw['fin_id'])) {
            $results[] = [
                'acct_num'   => $withdraw['acct_num'] ?? null,
                'result_cd'  => 953,
                'result_msg' => '유효하지 않은 금융정보',
            ];
            continue;
        }
        $bankAccount = $accounts[$withdraw['acct_num']];

        // 파라미터 구성
        $params = [
            'brand_id'            => $brandId,
            'fin_id'              => $withdraw['fin_id'],
            'oper_id'             => $operId,
            'amount'              => $withdraw['withdraw_amount'],
            'acct_num'            => $bankAccount->acct_num,
            'acct_name'           => $bankAccount->acct_name,
            'acct_bank_name'      => $bankAccount->acct_bank_name,
            'acct_bank_code'      => $bankAccount->acct_bank_code,
            'withdraw_book_time'  => $withdraw['withdraw_book_time'],
            'created_at'          => $now,
            'updated_at'          => $now,
        ];

        // 외부 API 호출
        try
            {
            $res = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/book-withdraw',$params);
                if ($res['code'] === 201)
                {
                    if ($res['body']['result_cd'] === '0000') {
                        $results[] = [
                            'acct_num'   => $withdraw['acct_num'],
                            'result_cd'  => 100,
                            'result_msg' => '이체 예약 완료',
                        ];
                        $success_count++;
                    } else {
                        $results[] = [
                            'acct_num'   => $withdraw['acct_num'],
                            'result_cd'  => $res['body']['result_cd'],
                            'result_msg' => $res['body']['result_msg'],
                        ];
                    }
                } else {
                    $results[] = [
                        'acct_num'   => $withdraw['acct_num'],
                        'result_cd'  => 502,
                        'result_msg' => $res['body']['result_msg'] ?? '이체 예약 에러',
                    ];
                }
            } catch (\Exception $e) {
                error($withdraw, 'book-withdraw-job-batch' . $e->getMessage());
                $results[] = [
                    'acct_num'   => $withdraw['acct_num'],
                    'result_cd'  => 500,
                    'result_msg' => '이체 예약 내부 처리 에러: ' . $e->getMessage(),
                ];
            }
        }
        $failed_count = $rows->count() - $success_count;
        $message = "총 {$rows->count()}건 중 {$success_count}건 성공";

        return $this->extendResponse(1, $message, [
            'total'   => $rows->count(),
            'success' => $success_count,
            'failed'  => $failed_count,
            'details' => $results,
        ]);
    }
}
