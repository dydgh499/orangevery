<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\Transaction\WithdrawAPI;
use App\Http\Controllers\Utils\Bank;

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

    public function bookWithdraw($request)
    {
        $datas = $request->data();
        $rows = collect($datas);
        $now = now();
        $brand_id  = $request->user()->brand_id;
        $finances = brandFilter(new FinanceVan, $request)
            ->whereIn('id', $rows->pluck('fin_id'))
            ->pluck('id')->all();
        $accounts = brandFilter(new BankAccount, $request)
            ->whereIn('acct_num', $rows->pluck('acct_num'))
            ->get();
        $account_valid = $accounts->pluck('acct_num')->all();
        
        $success = [];
        $fails = [];
        foreach ($rows as $row) 
        {
            if (in_array($row['fin_id'], $finances))
            {
                if (in_array($row['acct_num'], $account_valid))
                {
                    try
                    {
                        $account = $accounts->firstWhere('acct_num', $row['acct_num']);
                        $params = array_merge($this->getBankParams($account), $this->getBookWithdrawParams($row, $request, $now));
                        $res = WithdrawAPI::bookWithdraw($params);
                        if ($res['code'] === 201)
                        {
                            if ($res['body']['result_cd'] === '0000')
                            {
                                $success[] = array_merge(['acct_num' => $row['acct_num']], $res['body']);
                            }
                            else
                            {
                                $fails[] = array_merge(['acct_num' => $row['acct_num']], $res['body']);
                            }
                        }
                        else
                        {
                            $message = isset($res['body']['result_msg']) ? $res['body']['result_msg'] : '이체 예약 에러';
                            $fails[] = array_merge(['acct_num' => $row['acct_num']], ['message' => $message]);
                        }
                    }
                    catch (\Exception $e)
                    {
                        error($row, 'book-withdraw-job-batch'.$e->getMessage());
                        $fails[] = array_merge(['acct_num' => $row['acct_num']], ['message' => '이체 예약 내부 처리 에러']);
                    }
                }
                else
                    $fails[] = ['acct_num' => $row['acct_num'], 'message' => '등록되지 않은 계좌'];
            }
            else
                $fails[] = array_merge(['acct_num' => $row['acct_num']], ['message' => '유효하지 않은 금융정보']);
        }
        return [$success, $fails, $rows->count()];
    }

    public function getBookWithdrawParams($row, $request, $now)
    {
        return [
                'brand_id'           => $request->user()->brand_id,
                'fin_id'             => $row['fin_id'],
                'oper_id'            => $request->user()->id,
                'amount'             => $row['withdraw_amount'],
                'withdraw_book_time' => $row['withdraw_book_time'],
                'created_at'         => $now,
                'updated_at'         => $now,
        ];
    }

    public function getBankParams($account)
    {
        return [
            'acct_name'         => $account['acct_name'],
            'acct_num'          => $account['acct_num'],
            'acct_bank_code'    => $account['acct_bank_code'],
            'acct_bank_name'    => Bank::getBankName($account['acct_bank_code']),
        ];
    }

    
    /**
     * 계좌 출금 예약 테스트(등록되지 않은 계좌 있을시 출금 예약 실패)
     */
    public function register(BulkWithdrawBookRequest $request)
    {
        [$success, $fails, $counts] = $this->bookWithdraw($request);
        if($fails)
        {
            return $this->apiResponse("9999", '이체 예약에 실패했습니다.', [
                'total' => $counts,
                'fails' => $fails,
            ]);
        }
        else
        {
            return $this->apiResponse("0000", '이체 예약에 성공했습니다.', [
                'total' => $counts,
                'success' => $success,
            ]);
        }
    }
}
