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
use App\Http\Controllers\Option\Withdraw\CMSTransactionBookInterface;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Utils\FinanceVanUtil;
use App\Jobs\Realtime\RealtimeWrapper;
use App\Jobs\BookWithdraw;
use Carbon\Carbon;

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
     * 계좌 출금 예약 테스트(등록되지 않은 계좌 제외 출금 요청)
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
        $not_exist_accounts = [];

        foreach($datas as $data) 
        {
            // $result 초기화
            $result = [
                'acct_num' => $data['acct_num'] ?? null,
                'result_cd' => 100, // 기본 성공 코드
                'result_msg' => ''
            ];

            // 1. 계좌번호 존재 여부 확인
            $bankAccount = BankAccount::where('acct_num', $data['acct_num'])
                ->where('brand_id', $brand_id)
                ->first();

            if(!$bankAccount) {
                $result['result_cd'] = 952;
                $result['result_msg'] = '존재하지 않는 계좌번호';
                $results[] = $result;
                $not_exist_accounts[] = $data['acct_num'];
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
                'created_at' => $current,
                'updated_at' => $current,
            ];

            // 4. 등록 처리
            $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->cms_transaction_books, [$params], 'fin_id',$current, $brand_id);

            // [추가] 성공 결과 업데이트
            $result['result_cd'] = 100;
            $result['result_msg'] = '성공';
            $results[] = $result;
            $success_count++;
        }

        // 5. 최종 응답
        $failed_count = count($datas) - $success_count;
        $not_exist_accounts_str = '';
        
        if (!empty($not_exist_accounts)) {
            $not_exist_accounts_str = " (존재하지 않는 계좌번호: " . implode(", ", $not_exist_accounts) . ")";
        }
        
        $message = "총 " . count($datas) . "건 중 {$success_count}건 성공" . $not_exist_accounts_str;
        return $this->extendResponse(1, $message, [
            'total' => count($datas),
            'success' => $success_count,
            'failed' => $failed_count,
            'details' => $results,
            'not_exist_accounts' => $not_exist_accounts
        ]);
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
            // 4. 외부 API 호출 대체 - 내부 로직 직접 호출
            try {
                [$code, $finance_van, $privacy] = FinanceVanUtil::getThirdPartyInfo((object)$params, (object)$params);
                if ($code === '0000') {
                    $rt = new RealtimeWrapper($finance_van, $privacy, 4, $params['withdraw_book_time']);

                    if ($rt->service) {
                        // ✅ 예약 시간 처리
                        $delay = Carbon::parse($params['withdraw_book_time']);
                        $job = new BookWithdraw($finance_van, $privacy, $params['amount'], $params['note'], $params['withdraw_book_time']);
                        $job_id = Bus::dispatch($job->onConnection('redis')->onQueue('realtime')->delay($delay));
    
                        // ✅ Redis에 job_id 저장 (예약 취소/추적용)
                        $diff_seconds = Carbon::now()->diffInSeconds($delay);
                        if ($diff_seconds > 0) {
                            Redis::set("book-withdraw-" . $params['trans_seq_num'], $job_id, 'EX', $diff_seconds);
                        }
                            
                        // ✅ 예약 이력 기록
                        app(ActivityHistoryInterface::class)->batchAdd(
                            $this->target,         // 또는 context에 맞는 target 문자열
                            $this->cms_transaction_books,             // 실제 예약 관련된 테이블명 또는 키
                            [$params],                                // 예약에 사용된 파라미터
                            'fin_id',                                 // 기준 컬럼명
                            $current,                                 // 등록 시간
                            $brand_id                                 // 브랜드 ID
                        );
                        $results[] = [
                            'acct_num' => $data['acct_num'] ?? null,
                            'result_cd' => 100,
                            'result_msg' => '이체 예약 완료',
                            'job_id' => $job_id
                        ];
                        $success_count++;
                    } else {
                        // 금융밴 실패
                        $results[] = [
                            'acct_num' => $data['acct_num'] ?? null,
                            'result_cd' => 'PV406',
                            'result_msg' => '찾을 수 없는 타입입니다.'
                        ];
                    }
                } else {
                    $results[] = [
                        'acct_num' => $data['acct_num'] ?? null,
                        'result_cd' => $code,
                        'result_msg' => '개인정보 또는 실시간 모듈 정보가 매칭되지 않았습니다.'
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

    public function batchRemove(Request $request)
    {
        $ids = is_array($request->input('selected_idxs')) ? $request->input('selected_idxs') : [];

        Log::info('[batchRemove] 요청된 ID 목록', ['ids' => $ids]);
        // 선택된 ID로 trans_seq_num 조회
        $trx_nums = CMSTransactionBooks::whereIn('id', $ids)
        ->get()
        ->pluck('trans_seq_num')
        ->values()
        ->toArray();

        Log::info('[batchRemove] 추출된 trans_seq_num 목록', ['trx_nums' => $trx_nums]);
        // 실제 큐에서 예약된 job 삭제
        [$success, $fails, $not_founds] = CMSTransactionBookInterface::cancelJobs($trx_nums);
        
        Log::info('[batchRemove] 취소 결과', [
            'success' => $success,
            'fails' => $fails,
            'not_found' => $not_founds
        ]);
    
        // 활동 이력 기록
        $query = CMSTransactionBooks::whereIn('trans_seq_num', $success)->get();
        app(ActivityHistoryInterface::class)->destory($this->target, $query, 'id');
    
        $message = count($success) . "건 삭제 완료";
        if (count($fails) || count($not_founds)) {
            $message .= ". 일부 실패/미발견 항목 존재";
        }
    
        return $this->extendResponse(1, $message, [
            'success' => $success,
            'fails' => $fails,
            'not_found' => $not_founds
        ]);
    }
    
    /**
     * 일괄삭제
     */
    /*
    public function batchRemove(Request $request)
    {        
        // 출금예약 내역 쿼리 가져오기
        $query = $this->transactionBookBatch($request);
        // 활동 기록 및 삭제 실행
        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'id');
        return $this->extendResponse($row ? 1 : 990, $row ? $row.'개의 출금예약이 삭제되었습니다.' : '삭제된 출금예약 내역이 존재하지 않습니다.');
    }
    */
}
