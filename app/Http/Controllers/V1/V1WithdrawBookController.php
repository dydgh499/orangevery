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
use App\Http\Controllers\Option\Withdraw\CMSTransactionBookInterface;
use App\Http\Controllers\Option\Withdraw\CMSTransactionValidate;
use Illuminate\Support\Facades\Log;

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

    /**
     * 단일삭제
     */
    public function cancelJob(Request $request)
    {
        $validated = $request->validate(['trx_id.*' => 'required']);
        [$goods, $fails, $not_founds] = CMSTransactionBookInterface::cancelJobs($request->trx_id);

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
