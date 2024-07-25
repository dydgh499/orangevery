<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\CollectWithdraw;
use App\Models\Service\ExceptionWorkTime;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Requests\Manager\Service\ExceptionWorkTimeRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group exception work time API
 *
 */
class ExceptionWorkTimeController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $work_times;

    public function __construct(ExceptionWorkTime $work_times)
    {
        $this->work_times = $work_times;
    }

    public function index(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 999,
        ]);
        $query  = $this->work_times->where('brand_id', $request->user()->brand_id);
        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }
}
