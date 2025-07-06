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
                        $error = array_merge($res, ['acct_num' => $unkonwn_bank_account]);
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
            return $this->apiResponse("9999", $error['body']['message']." (".$error['acct_num'].")");
        else
            return $this->apiResponse("0000", '성공하였습니다.', $ids);
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
