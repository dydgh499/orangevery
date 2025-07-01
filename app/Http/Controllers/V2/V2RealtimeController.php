<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\FinanceVanUtil;

use App\Models\Transaction;
use App\Models\Merchandise;
use App\Models\Service\FinanceVan;

use App\Http\Traits\Util\APITrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;

use Carbon\Carbon;
use App\Jobs\Realtime\RealtimeWrapper;
use App\Jobs\Realtime\ScheduledWithdraw;
use App\Enums\RealtimeCustomFailCode;
use App\Http\Controllers\Option\Withdraw\VirtualAccountValidate;

class V2RealtimeController extends Controller
{
    use APITrait;

    public $db = null;

    public function __construct()
    {
        $this->db = DB::connection('onetest');
    }

    static public function pushNotiUrl($ids, $type)
    {
        $finance_vans = DB::connection('onetest')
                ->table('finance_vans')
                ->whereIn('id', $ids)
                ->get();
        $finance_vans = json_decode(json_encode($finance_vans), true);
        foreach($finance_vans as $finance_van)
        {
            $rt = new WelcomeWrap($finance_van, [], 2);
            echo($rt->pushNotiUrl($type)."\n\n");
        }
    }

    public function noti(Request $request, $finance_name)
    {
        $path   = "App\Http\Controllers\PG\Noti\\Realtime\\".$finance_name;
        if(class_exists($path))
        {
            $pg = new $path($this->db);
            $pg->process($request);
        }
        else
        {
            $code = -10;
            logging(['code'=>$code], 'not found Finance VAN');
        }
    }

    public function getBalance(Request $request)
    {
        $finance_van = $request->all();
        $rt = new RealtimeWrapper($finance_van, $finance_van, -1);
        if($rt->service)
        {
            if($finance_van['finance_company_num'] === 1)
                $rt->service->trsc_no = VirtualAccountValidate::getTrxNum();
            else if($finance_van['finance_company_num'] === 5)
                $rt->service->trsc_no = $rt->service->getWithdrawTrxNum();

            $res = $rt->service->getBalance();
            $result = [
                'result_cd'  => $res['RESP_CD'],
                'result_msg' => $res['RESP_MSG'],
                'data'       => [],
            ];
            if($result['result_cd'] === '0000')
                $result['data'] = $res;
        }
        else
        {
            $result = [
                'result_cd' => 'PV406',
                'result_msg' => '찾을 수 없는 타입입니다.',
                'data' => [],
            ];
        }
        return $this->response('', $result, 200);
    }
}
