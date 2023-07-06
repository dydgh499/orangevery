<?php

namespace App\Http\Controllers\Log;

use App\Models\Logs\Transactions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

class DashbardController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;

    /*
     * 월별 거래 분석(10개월)
     */
    public function monthlyTranAnalysis(Request $request)
    {

    }
    
    /*
     * 지난달 대비 거래상승률 분석(매출, 정산)
     */
    public function upSideTranAnalysis(Request $request)
    {

    }

    /*
     * 결제모듈 사용량 분석(6개월) (장비, 수기, 인증, 간편)
     */
    public function payModuleUsageAnalysis(Request $request)
    {

    }

    /*
     * 가맹점 증감률 분석(저번달 대비)
     */
    public function upSideMchtAnalysis(Request $request)
    {

    }
}
