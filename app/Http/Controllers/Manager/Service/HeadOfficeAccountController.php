<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\HeadOfficeAccount;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Requests\Manager\Service\HeadOfficeAccountRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Head Office Account API
 *
 * 가상계좌 출금 API 입니다.
 */
class HeadOfficeAccountController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $head_office_accounts;

    public function __construct(HeadOfficeAccount $head_office_accounts)
    {
        $this->head_office_accounts = $head_office_accounts;
    }

    public function index(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 999,
        ]);
        $query  = $this->head_office_accounts->where('brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
}
