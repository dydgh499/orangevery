<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Utils\Bank;

use App\Models\BankAccount;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkBankAccountRequest;
use App\Http\Requests\Manager\BulkRegister\BulkOwnerCheckRequest;
use App\Http\Controllers\Manager\Transaction\WithdrawAPI;


use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Utils\Comm;
use Illuminate\Http\Request;

/**
 * @group Bank-Account-Batch-Updater API
 *
 * 계좌등록 일괄 업데이트 group 입니다.
 */
class BatchUpdateBankAccountController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $account;

    public function __construct(BankAccount $account)
    {
        $this->account = $account;
        $this->target = '계좌등록';
    }
    
    
    public function getBankAccountParams($data)
    {
        return [
            'acct_name'         => $data['acct_name'],
            'acct_num'          => $data['acct_num'],
            'acct_bank_code'    => $data['acct_bank_code'],
            'acct_bank_name'    => Bank::getBankName($data['acct_bank_code']),
        ];
    }

    public function addBankAccountObjects($request, $datas)
    {
        $current = date('Y-m-d H:i:s');
        $datas = $datas->map(function ($data) use($current, $request) {
            $data['brand_id']   = $request->user()->brand_id;
            $data['oper_id']    = $request->user()->id;
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        return app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->account, $datas, 'acct_num', $current, $request->user()->brand_id);
    }

    public function getNewAccounts($request)
    {
        $news  = [];
        $error = null;
        $datas = $request->data();
        
        $unkonwn_bank_accounts  = $datas->pluck('acct_num')->unique()->all();
        $exist_bank_accounts    = brandFilter(new BankAccount, $request)
            ->whereIn('acct_num', $datas->pluck('acct_num')->all())
            ->pluck('acct_num')
            ->all();

        foreach($unkonwn_bank_accounts as $unkonwn_bank_account)
        {
            if($error)
                break;
            if(in_array($unkonwn_bank_account, $exist_bank_accounts) === false)
            {
                $data = $datas->firstWhere('acct_num', $unkonwn_bank_account);
                if($data)
                {
                    $params = $this->getBankAccountParams($data);
                    $res = WithdrawAPI::ownerCheck($params);
                    if($res['body']['result'] === 100)
                        $news[] = $params;
                    else
                        $error = $res;
                }
                else
                    error($unkonwn_bank_account, '존재하지 않는 계좌');
            }
        }
        return [$news, $error];
    }

    /**
     * 예금주 검증
     *
     * 운영자 이상 가능
     */
    public function ownerCheck(BulkOwnerCheckRequest $request)
    {
        [$news, $error] = $this->getNewAccounts($request);
        $ids = $this->addBankAccountObjects($request, collect($news));
        if($error)
            return $this->response(9999, $error['body']['message']);
        else
            return $this->response(1, $ids);
    }


    /**
     * 대량등록 예금주 검증 및 중복 검사 후 자동 등록
     * 
     * 운영자 이상 가능
     * 한 개의 계좌라도 예금주 검증 실패 시 전체 등록 취소
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
            })->map(function($item) {
                return is_array($item) ? $item : $item->toArray();
            });
            
            // 3. 등록할 데이터가 하나도 없으면 안내 메시지 반환
            if ($filtered_datas->isEmpty()) {
                return $this->extendResponse(1, '모든 계좌번호가 이미 등록되어 있습니다.');
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
                $ownerCheckResult = Comm::post(env('NOTI_URL', 'http://localhost:81').'/api/v2/realtimes/owner-check', json_encode($accountData, JSON_UNESCAPED_UNICODE));
                // 예금주 조회 실패인 경우 실패 계좌와 메세지
                if ($ownerCheckResult['body']['result'] !== 100) {
                    $failed_accounts[] = [
                        'acct_num' => $data['acct_num'],
                        'message' => $ownerCheckResult['body']['message']
                    ];
                } else {
                }
            }
            
            // 5. 하나라도 검증에 실패한 계좌가 있으면 모든 등록 취소
            if (!empty($failed_accounts)) {
                // 계좌번호와 실패 이유를 함께 표시하는 배열 생성
                $failed_details = [];
                foreach ($failed_accounts as $failed) {
                    $failed_details[] = $failed['acct_num'] . ' (' . $failed['message'] . ')';
                }
                
                // 계좌번호와 실패 이유를 함께 문자열로 변환
                $failed_details_str = implode(', ', $failed_details);
                
                return $this->extendResponse(990, '예금주 검증에 실패한 계좌가 있어 모든 등록이 취소되었습니다: ' . $failed_details_str, ['failed' => $failed_accounts]);
            }
            
            // 6. 모든 계좌가 검증에 성공했을 경우만 등록 처리
            $uniqueSuccessAccounts = collect($success_accounts)
                ->keyBy('acct_num')   // acct_num 중복 제거
                ->values()
                ->all();

            // ② batchAdd 호출 시 중복 키를 acct_num 으로 지정
            $ids = app(ActivityHistoryInterface::class)->batchAdd(
                $this->target,
                $this->account,
                $uniqueSuccessAccounts,
                'acct_num',
                $current,
                $brand_id
            );

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
    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $ids = $request->input('selected_idxs', []);
        $query = $this->account->whereIn('id', $ids);
        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'id');
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 은행계좌가 존재하지 않습니다.');
    }
}
