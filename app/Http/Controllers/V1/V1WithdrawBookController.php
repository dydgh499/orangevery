<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Service\CMSTransactionBooks;

use App\Http\Traits\Util\APITrait;

use Illuminate\Http\Request;
use App\Http\Controllers\Option\Withdraw\CMSTransactionBookInterface;
use Illuminate\Support\Facades\Log;

class V1WithdrawBookController extends Controller
{
    use APITrait;
    protected $cms_transaction_books;

    public function __construct(CMSTransactionBooks $cms_transaction_books)
    {
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
