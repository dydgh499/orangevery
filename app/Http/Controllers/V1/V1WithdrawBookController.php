<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Util\FinanceVanUtil;

use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\CancelDeposit;
use App\Models\Service\FinanceVan;

use App\Models\Withdraws\VirtualAccount;
use App\Models\Withdraws\VirtualAccountHistory;
use App\Models\Service\CMSTransactionBooks;

use App\Http\Traits\Util\APITrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\Realtime\RealtimeWrapper;
use App\Enums\RealtimeCustomFailCode;
use App\Http\Controllers\Option\Withdraw\VirtualAccountDepositInterface;
use App\Http\Controllers\Option\Withdraw\CMSTransactionWithdrawBookInterface;
use App\Http\Controllers\Option\Withdraw\CMSTransactionBookInterface;
use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;

class V1WithdrawBookController extends Controller
{
    use APITrait;
    protected $virtual_accounts, $virtual_account_histories, $cms_transaction_books;

    public function __construct(VirtualAccount $virtual_accounts,VirtualAccountHistory $virtual_account_histories, CMSTransactionBooks $cms_transaction_books)
    {
        $this->virtual_accounts = $virtual_accounts;
        $this->virtual_account_histories = $virtual_account_histories;
        $this->cms_transaction_books = $cms_transaction_books;
    }

    public function _collect($virtual_account, $withdraw_amount, $user, $request)
    {
        if((int)$request->input('fee_apply', 1) === 0)
            $virtual_account['withdraw_fee'] = 0;

        $result = CMSTransactionValidate::getAbleBalance($virtual_account);
        if($result['withdraw_able_amount'] - $virtual_account['withdraw_fee'] >= $withdraw_amount)
        {
            $trans = ['trx_id' => null];
            $trans_amount = $withdraw_amount * -1;
            $withdraw_history = CMSTransactionWithdrawBookInterface::addWithdrawHistory($trans, $virtual_account, 0, now());
            if($withdraw_history['id'])
            {
                $job_id = CMSTransactionWithdrawBookInterface::bookWithdraw($virtual_account, $withdraw_history, $user, 0, $trans_amount);
                if($job_id)
                    return $this->apiErrorResponse('0000', '입금요청을 성공하였습니다.', 201);
                else
                    return $this->apiErrorResponse("PV406", "찾을 수 없는 타입입니다.", 409);
            }
            else
                return $this->apiErrorResponse('9999', '출금데이터 생성 실패.');
        }
        else
            return $this->apiErrorResponse('9999', '출금가능금액보다 출금액이 높습니다.');
    }

    /**
     * 모아서 출금요청
     *
     * 출금가능한금액을 조회후 모아서출금합니다.
     */
    public function collect(Request $request)
    {
        $validated = $request->validate(['va_id' => 'required|integer', 'withdraw_amount' => 'required|integer']);
        $virtual_account = CMSTransactionValidate::getVirtualAccount($request->va_id);
        if($virtual_account)
        {
            $user = Merchandise::where('id', $virtual_account['user_id'])->where('is_delete', false)->first();
            if($user)
                return $this->_collect($virtual_account, $request->withdraw_amount, $user, $request);
            else
                return $this->apiErrorResponse('PV406', '회원을 찾을 수 없습니다.');
        }
        else
            return $this->apiErrorResponse('PV406', '정산지갑을 찾을 수 없습니다.');
    }

    /**
     * 단일삭제
     */
    public function cancelJob(Request $request)
    {
        $validated = $request->validate(['trx_id.*' => 'required']);
        [$goods, $fails, $not_founds] = CMSTransactionBookInterface::cancelJobs($request->trx_ids);

        if (count($fails)) {
            $message = "이체 예약취소에 실패한 거래건들이 존재합니다.<br><br>" . json_encode($fails, JSON_UNESCAPED_UNICODE);
            return $this->apiErrorResponse('PV451', $message);
        } else if (count($not_founds)) {
            $message = "이체 예약목록에서 찾을 수 없는 거래건들이 존재합니다.<br><br>" . json_encode($not_founds, JSON_UNESCAPED_UNICODE);
            return $this->apiErrorResponse('PV451', $message);
        } else {
            return $this->apiErrorResponse('0000', '이체 예약취소를 성공하였습니다', 201);
        }
    }
}
