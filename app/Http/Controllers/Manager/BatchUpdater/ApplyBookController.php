<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Models\Merchandise;
use App\Models\Merchandise\NotiUrl;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\MerchandiseColumnApplyBook;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthOperatorIP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ApplyBookController extends Controller
{
    public function __invoke()
    {
        
    }
}
