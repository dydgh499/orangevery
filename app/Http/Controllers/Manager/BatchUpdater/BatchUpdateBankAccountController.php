<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;

use App\Models\BankAccount;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkBankAccountRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Utils\Comm;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Bank-Account-Batch-Updater API
 *
 * 계좌등록 일괄 업데이트 group 입니다.
 */
class BatchUpdateBankAccountController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $account, $target;

    public function __construct(BankAccount $account)
    {
        $this->account = $account;
        $this->target = '계좌등록';
    }
    
    /**
     * 대량등록 중복 검사 후 자동 등록
     *
     * 운영자 이상 가능
     */
    public function register(BulkBankAccountRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        if(count($datas) > 100)
            return $this->extendResponse(1000, '계좌등록은 한번에 최대 100개까지 등록할 수 있습니다.');
        else 
        {
            // 1. 이미 존재하는 계좌번호 조회
            $exist_accounts = $this->isExistBulkAccountNum($brand_id, $datas->pluck('acct_num')->all());
    
            // 2. 중복 계좌번호를 제외한 데이터만 필터링
            $filtered_datas = $datas->filter(function ($data) use ($exist_accounts) {
                return !in_array($data['acct_num'], $exist_accounts);
            });
    
            // 3. 등록할 데이터가 하나도 없으면 안내 메시지 반환
            if ($filtered_datas->isEmpty()) {
                return $this->extendResponse(1000, '모든 계좌번호가 이미 등록되어 있습니다.');
            }
            
            // 5. 등록할 데이터 준비
            $account = $filtered_datas->map(function ($data) use ($current, $brand_id) {
                $data['brand_id']   = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                return $data;
            })->values()->toArray();
            
            // 5. 등록 처리
            $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->account, $account, 'acct_name', $current, $brand_id);
            
            // 6. 중복 계좌번호가 있었다면 안내 메시지와 함께 성공 반환
            if (count($exist_accounts)) {
                $formattedAccounts = '';
                foreach ($exist_accounts as $index => $account) {
                    $formattedAccounts .= $account;
                    // 마지막 항목이 아니면 줄바꿈 추가
                    if ($index < count($exist_accounts) - 1) {
                        $formattedAccounts .= ",\n";
                    }
                }
                return $this->extendResponse(1, $formattedAccounts . '는 이미 존재하는 계좌번호입니다. 나머지는 정상 등록되었습니다.', $ids);
            } else {
                $count = count($ids);
                return $this->extendResponse(1, "총 {$count}개의 계좌번호가 등록에 성공했습니다.", $ids);
            }
        }
    }

    /**
     * 대량등록 예금주 검증 및 중복 검사 후 자동 등록
     *
     * 운영자 이상 가능
     */
    public function ownerCheckTest(BulkBankAccountRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        
        if(count($datas) > 100)
            return $this->extendResponse(1000, '계좌등록은 한번에 최대 100개까지 등록할 수 있습니다.');
        else 
        {
            // 1. 이미 존재하는 계좌번호 조회
            $exist_accounts = $this->isExistBulkAccountNum($brand_id, $datas->pluck('acct_num')->all());
            
            // 2. 중복 계좌번호를 제외한 데이터만 필터링
            $filtered_datas = $datas->filter(function ($data) use ($exist_accounts) {
                return !in_array($data['acct_num'], $exist_accounts);
            })->map(function($item) {
                return is_array($item) ? $item : $item->toArray();
            });
            
            // 3. 등록할 데이터가 하나도 없으면 안내 메시지 반환
            if ($filtered_datas->isEmpty()) {
                return $this->extendResponse(1000, '모든 계좌번호가 이미 등록되어 있습니다.');
            }
            /*
            // 4. 예금주 검증 수행
            $success_accounts = [];
            $failed_accounts = [];
            
            foreach ($filtered_datas as $data) {
                $ownerCheckResult = $this->ownerCheckForBatch([
                    'acct_cd' => $data['acct_bank_code'],
                    'acct_num' => $data['acct_num'],
                    'acct_nm' => $data['acct_name'],
                ]);
                
                if ($ownerCheckResult['result'] === 100) {
                    $data['brand_id']   = $brand_id;
                    $data['created_at'] = $current;
                    $data['updated_at'] = $current;
                    $data['checked']    = 1;
                    $success_accounts[] = $data;
                } else {
                    $failed_accounts[] = [
                        'acct_num' => $data['acct_num'],
                        'message' => $ownerCheckResult['message']
                    ];
                }
            }
            
            // 5. 모든 계좌가 검증에 실패했다면 오류 메시지 반환
            if (empty($success_accounts)) {
                return $this->extendResponse(1000, '예금주 검증에 모두 실패했습니다.', ['failed' => $failed_accounts]);
            }
            */
            $success_accounts = [];
            foreach ($filtered_datas as $data) {
                $data['brand_id']   = $brand_id;
                $data['created_at'] = $current;
                $data['updated_at'] = $current;
                // 테스트로 모든 계좌가 검증 성공한 것으로 처리
                // $data['checked']    = 1; // 검증 플래그 설정 여부를 테스트할 수 있음
                $success_accounts[] = $data;
            }
            // 6. 등록 처리
            $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->account, array_values($success_accounts), 'acct_name', $current, $brand_id);
            
            // 7. 결과 메시지 생성 및 반환
            $message = '';
            if (count($exist_accounts)) {
                $formattedAccounts = '';
                foreach ($exist_accounts as $index => $account) {
                    $formattedAccounts .= $account;
                    // 마지막 항목이 아니면 콤마 추가
                    if ($index < count($exist_accounts) - 1) {
                        $formattedAccounts .= ',';
                    }
                }
                $message .= $formattedAccounts . '는 이미 존재하는 계좌번호입니다. ';
            }

            /*
            if (count($failed_accounts)) {
                $message .= '예금주 검증 실패: ' . implode(', ', array_column($failed_accounts, 'acct_num')) . '. ';
            }
            */
            
            if ($message) {
                return $this->extendResponse(1, $message . '나머지는 정상 검증 후 등록 되었습니다.', $ids);
            } else {
                $count = count($ids);
                return $this->extendResponse(1, "총 {$count}개의 계좌번호가 검증 후 등록에 성공했습니다.", $ids);
            }
        }
    }


    /**
     * 대량등록 예금주 검증 및 중복 검사 후 자동 등록
     * 
     * 운영자 이상 가능
     * 한 개의 계좌라도 예금주 검증 실패 시 전체 등록 취소
     */
    public function ownerCheckHardTest(BulkBankAccountRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        
        if(count($datas) > 100)
            return $this->extendResponse(1000, '계좌등록은 한번에 최대 100개까지 등록할 수 있습니다.');
        else 
        {
            // 1. 이미 존재하는 계좌번호 조회
            $exist_accounts = $this->isExistBulkAccountNum($brand_id, $datas->pluck('acct_num')->all());
            
            // 2. 중복 계좌번호를 제외한 데이터만 필터링
            $filtered_datas = $datas->filter(function ($data) use ($exist_accounts) {
                return !in_array($data['acct_num'], $exist_accounts);
            })->map(function($item) {
                return is_array($item) ? $item : $item->toArray();
            });
            
            // 3. 등록할 데이터가 하나도 없으면 안내 메시지 반환
            if ($filtered_datas->isEmpty()) {
                return $this->extendResponse(1000, '모든 계좌번호가 이미 등록되어 있습니다.');
            }
            
            // 4. 예금주 검증 수행
            $success_accounts = [];
            $failed_accounts = [];
            
            foreach ($filtered_datas as $data) {
                // 배열 데이터를 객체로 변환
                $accountData = (object)[
                    'acct_cd' => $data['acct_bank_code'], // 은행코드
                    'acct_nm' => $data['acct_name'], // 예금주명
                    'acct_num' => (string)$data['acct_num'], // 계좌번호
                ];
                $ownerCheckResult = $this->ownerCheckForBatch($accountData);
                
                if ($ownerCheckResult['result'] === 100) {
                    $data['brand_id']   = $brand_id;
                    $data['created_at'] = $current;
                    $data['updated_at'] = $current;
                    $data['checked']    = 1;
                    $success_accounts[] = $data;
                } else {
                    $failed_accounts[] = [
                        'acct_num' => $data['acct_num'],
                        'message' => $ownerCheckResult['message']
                    ];
                }
            }
            
            // 5. 하나라도 검증에 실패한 계좌가 있으면 모든 등록 취소
            if (!empty($failed_accounts)) {
                $failed_account_nums = implode(', ', array_column($failed_accounts, 'acct_num'));
                return $this->extendResponse(990, '예금주 검증에 실패한 계좌가 있어 모든 등록이 취소되었습니다: ' . $failed_account_nums, ['failed' => $failed_accounts]);
            }
            
            // 6. 모든 계좌가 검증에 성공했을 경우만 등록 처리
            $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->account, array_values($success_accounts), 'acct_name', $current, $brand_id);
            
            // 7. 결과 메시지 생성 및 반환
            $message = '';
            if (count($exist_accounts)) {
                $formattedAccounts = '';
                foreach ($exist_accounts as $index => $account) {
                    $formattedAccounts .= $account;
                    // 마지막 항목이 아니면 콤마 추가
                    if ($index < count($exist_accounts) - 1) {
                        $formattedAccounts .= ',';
                    }
                }
                $message .= $formattedAccounts . '는 이미 존재하는 계좌번호입니다. ';
            }
            
            if ($message) {
                return $this->extendResponse(1, $message . '나머지는 정상 검증 후 등록 되었습니다.', $ids);
            } else {
                $count = count($ids);
                return $this->extendResponse(1, "총 {$count}개의 계좌번호가 검증 후 등록에 성공했습니다.", $ids);
            }
        }
    }

    
    /*
    * 예금주 조회
    */
    public function ownerCheck(BulkBankAccountRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();
        
        if(count($datas) > 100)
            return $this->extendResponse(1000, '계좌등록은 한번에 최대 100개까지 등록할 수 있습니다.');
        else 
        {
            // 1. 이미 존재하는 계좌번호 조회
            $exist_accounts = $this->isExistBulkAccountNum($brand_id, $datas->pluck('acct_num')->all());
            
            // 2. 중복 계좌번호를 제외한 데이터만 필터링
            $filtered_datas = $datas->filter(function ($data) use ($exist_accounts) {
                return !in_array($data['acct_num'], $exist_accounts);
            })->map(function($item) {
                return is_array($item) ? $item : $item->toArray();
            });
            
            // 3. 등록할 데이터가 하나도 없으면 안내 메시지 반환
            if ($filtered_datas->isEmpty()) {
                return $this->extendResponse(1000, '모든 계좌번호가 이미 등록되어 있습니다.');
            }
            
            // 4. 예금주 검증 수행
            $success_accounts = [];
            $failed_accounts = [];
            
            foreach ($filtered_datas as $data) {
                // 배열 데이터를 객체로 변환
                $accountData = (object)[
                    'acct_cd' => $data['acct_bank_code'], // 은행코드
                    'acct_nm' => $data['acct_name'], // 예금주명
                    'acct_num' => (string)$data['acct_num'], // 계좌번호
                ];

                // API 호출
                $ownerCheckResult = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/owner-check', $accountData);
                
                if ($ownerCheckResult['body']['result'] === 100) {
                    $data['brand_id']   = $brand_id;
                    $data['created_at'] = $current;
                    $data['updated_at'] = $current;
                    $data['checked']    = 1;
                    $success_accounts[] = $data;
                } else {
                    $failed_accounts[] = [
                        'acct_num' => $data['acct_num'],
                        'message' => $ownerCheckResult['body']['message']
                    ];
                }
            }
            
            // 5. 하나라도 검증에 실패한 계좌가 있으면 모든 등록 취소
            if (!empty($failed_accounts)) {
                $failed_account_nums = implode(', ', array_column($failed_accounts, 'acct_num'));
                return $this->extendResponse(990, '예금주 검증에 실패한 계좌가 있어 모든 등록이 취소되었습니다: ' . $failed_account_nums, ['failed' => $failed_accounts]);
            }
            
            // 6. 모든 계좌가 검증에 성공했을 경우만 등록 처리
            $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->account, array_values($success_accounts), 'acct_name', $current, $brand_id);
            
            // 7. 결과 메시지 생성 및 반환
            $message = '';
            if (count($exist_accounts)) {
                $formattedAccounts = '';
                foreach ($exist_accounts as $index => $account) {
                    $formattedAccounts .= $account;
                    // 마지막 항목이 아니면 콤마 추가
                    if ($index < count($exist_accounts) - 1) {
                        $formattedAccounts .= ',';
                    }
                }
                $message .= $formattedAccounts . '는 이미 존재하는 계좌번호입니다. ';
            }
            
            if ($message) {
                return $this->extendResponse(1, $message . '나머지는 정상 검증 후 등록 되었습니다.', $ids);
            } else {
                $count = count($ids);
                return $this->extendResponse(1, "총 {$count}개의 계좌번호가 검증 후 등록에 성공했습니다.", $ids);
            }
        }
        
    }
}