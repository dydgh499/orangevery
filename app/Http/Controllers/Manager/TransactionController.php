<?php

namespace App\Http\Controllers\Manager;

use App\Models\Transaction;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Settle\TransactionTrait;
use App\Http\Requests\Manager\TransactionRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Database\QueryException;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\HistoryType;

/**
 * @group Transaction API
 *
 * 거래 API 입니다.
 */
class TransactionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, TransactionTrait;
    protected $transactions;
    protected $target;
    public $cols;
    
    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
        $this->target = '매출';
        $this->cols = [
            'merchandises.mcht_name', 'merchandises.user_name', 'merchandises.nick_name',
            'merchandises.addr', 'merchandises.resident_num', 'merchandises.business_num', 
            'merchandises.use_saleslip_prov', 'merchandises.use_saleslip_sell', 'merchandises.use_collect_withdraw', 'merchandises.is_show_fee',
            'transactions.*',
            'payment_modules.note', 'payment_modules.use_realtime_deposit', 'payment_modules.cxl_type', 'payment_modules.fin_trx_delay',
            DB::raw("concat(trx_dt, ' ', trx_tm) AS trx_dttm"),
            DB::raw("concat(cxl_dt, ' ', cxl_tm) AS cxl_dttm"),
        ];
    }

    protected function getTransactionData($request, $query)
    {
        [$settle_key, $group_key] = $this->getSettleCol($request);
        if($settle_key == 'dev_settle_amount')
            $profit = DB::raw("($settle_key + dev_realtime_settle_amount) AS profit");
        else
            $profit = "$settle_key AS profit";
        array_push($this->cols, $profit);

        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        return $this->transPagenation($query, 'transactions', $this->cols, $page, $page_size);
    }

    public function commonSelect($request)
    {
        $search = $request->input('search', '');
        $query  = $this->transactions
            ->join('payment_modules', 'transactions.pmod_id', '=', 'payment_modules.id')
            ->join('merchandises', 'transactions.mcht_id', '=', 'merchandises.id')
            ->globalFilter();

        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('transactions.mid', 'like', "%$search%")
                    ->orWhere('transactions.tid', 'like', "%$search%")
                    ->orWhere('transactions.trx_id', 'like', "%$search%")
                    ->orWhere('transactions.appr_num', 'like', "%$search%")
                    ->orWhere('transactions.issuer', 'like', "%$search%")
                    ->orWhere('transactions.acquirer', 'like', "%$search%")
                    ->orWhere('payment_modules.note', 'like', "%$search%")
                    ->orWhere('merchandises.mcht_name', 'like', "%$search%")
                    ->orWhere('merchandises.resident_num', 'like', "%$search%")
                    ->orWhere('merchandises.business_num', 'like', "%$search%");
            });
        }
        $query = $this->transDateFilter($query, $request->s_dt, $request->e_dt, $request->use_search_date_detail);
        if($request->only_cancel && $request->only_cancel == 'true')
            $query = $query->where('transactions.is_cancel', true);
        if($request->has('mcht_settle_id'))
            $query = $query->where('transactions.mcht_settle_id', $request->mcht_settle_id);

        for ($i=0; $i < 6; $i++) { 
            $col = 'sales'.$i.'_settle_id';
            if($request->has($col))
                $query = $query->where('transactions.'.$col, $request->input($col));
        }
        return $query;
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        $query  = $this->commonSelect($request);
        [$settle_key, $group_key] = $this->getSettleCol($request);
        $cols = $this->getTotalCols($settle_key);
        $chart = $query->first($cols);
        $chart = $this->setTransChartFormat($chart);
        return $this->response(0, $chart);
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $query = $this->commonSelect($request);

        $with = [];
        if($request->use_realtime_deposit && $request->level == 10)
            $with[] = 'realtimes';
        if($request->use_cancel_deposit)
            $with[] = 'cancelDeposits';
        if(count($with))
            $query = $query->with($with);

        $data   = $this->getTransactionData($request, $query);
        $sales_ids      = globalGetUniqueIdsBySalesIds($data['content']);
        $salesforces    = globalGetSalesByIds($sales_ids);
        $data['content'] = globalMappingSales($salesforces, $data['content']);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(TransactionRequest $request)
    {
        try
        {
            $data = $request->data();
            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->create($data);
            operLogging(HistoryType::CREATE, $this->target, $data, "#".$res->id);
            return $this->response($res ? 1 : 990, ['id'=>$res->id]);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';                
            return $this->extendResponse(991, $msg);
        }
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, $id)
    {
        $data = $this->transactions->where('id', $id)->first();
        if($data)
        {
            $data->ps_fee = number_format($data->ps_fee * 100, 3);
            $data->mcht_fee = number_format($data->mcht_fee * 100, 3);
            $data->hold_fee = number_format($data->hold_fee * 100, 3);
            $data->sales5_fee = number_format($data->sales5_fee * 100, 3);
            $data->sales4_fee = number_format($data->sales4_fee * 100, 3);
            $data->sales3_fee = number_format($data->sales3_fee * 100, 3);
            $data->sales2_fee = number_format($data->sales2_fee * 100, 3);
            $data->sales1_fee = number_format($data->sales1_fee * 100, 3);
            $data->sales0_fee = number_format($data->sales0_fee * 100, 3);
            $data->dev_fee    = number_format($data->dev_fee * 100, 3);
        }
        else
            return $this->response(1000);
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(TransactionRequest $request, $id)
    {
        try
        {
            $data = $request->data();
            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->where('id', $id)->update($data);
            operLogging(HistoryType::UPDATE, $this->target, $data, "#".$id);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';                
            return $this->extendResponse(991, $msg);
        }
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, $id)
    {
        if($this->authCheck($request->user(), $id, 35))
        {
            $res = $this->transactions->where('id', $id)->delete();
            operLogging(HistoryType::DELETE, $this->target, ['id' => $id], "#".$id);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
        else
            return $this->response(951);
    }

    /**
     * 취소매출 생성
     *
     */
    public function cancel(TransactionRequest $request)
    {
        $data = $request->data();
        // TransactionRequest 에서 100을 먼저 나눠서 가져오기 떄문에 다시가져옴
        $data['ps_fee']  = $request->input('ps_fee', 0);
        $data['hold_fee']  = $request->input('hold_fee', 0);
        $data['mcht_fee']    = $request->input('mcht_fee', 0);
        $data['sales0_fee'] = $request->input('sales0_fee', 0);
        $data['sales1_fee'] = $request->input('sales1_fee', 0);
        $data['sales2_fee'] = $request->input('sales2_fee', 0);
        $data['sales3_fee'] = $request->input('sales3_fee', 0);
        $data['sales4_fee'] = $request->input('sales4_fee', 0);
        $data['sales5_fee'] = $request->input('sales5_fee', 0);
        try 
        {
            [$data] = $this->setSettleAmount([$data], $request->dev_settle_type);
            $res = $this->transactions->create($data);
            operLogging(HistoryType::CREATE, $this->target, $data, "#".$res->id);
            return $this->response(1);
        }
        catch(QueryException $ex)
        {
            $msg = $ex->getMessage();
            if(str_contains($msg, 'Duplicate entry'))
                $msg = '이미 같은 거래번호의 취소매출이 존재합니다.<br>해당 매출을 삭제하거나 거래번호를 수정한 후 다시 시도해주세요.';     
                
            return $this->extendResponse(991, $msg);
        }
    }

    /**
     * 수기결제
     *
     */
    public function handPay(Request $request)
    {
        $getYYMM = function($mmyy) {
            if(mb_strlen($mmyy, 'utf-8') == 4)
            {
                $first 	= substr($mmyy, 0, 2);
                $sec 	= substr($mmyy, 2, 2);
                return $sec.$first;
            }
            else
                return '';
        };

        $data = $request->all();
        $data['yymm'] = $getYYMM($data['yymm']);
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/hand';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
            return $this->response(1, $res['body']);
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);
    }

    /**
     * 결제취소
     *
     */
    public function payCancel(Request $request)
    {
        $data = $request->all();
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/online/pay/cancel';
        $res = post($url, $data);
        if($res['body']['result_cd'] === "0000")
            return $this->response(1, $res['body']);
        else
            return $this->extendResponse(1999, $res['body']['result_msg']);
    }

    /*
     * 노티 대량 재전송
     */
    public function batchRetry(Request $request)
    {
        $url = env('NOTI_URL', 'http://localhost:81').'/api/v2/noti/custom';
        $trans = $this->transactions->whereIn('id', $request->selected)->get();
        foreach($trans as $tran)
        {
            $res = $this->notiSender($url, $tran, '');
        }
        return $this->response(1);
    }

    public function _test()
    {
        $dev_settle_type = 0;
        $db_trans = $this->transactions
            ->where('brand_id', 2)
            ->whereIn('trx_id',
            [
                'ONhans002m01012303291723510093',
                'ONauto515m01012304210937340912',
                'ONauto515m01012304210937340912',
                'OFauto008m01012305010814250440',
                'OFauto008m01012305011105310599',
                'ONauto693m01012305011239500604',
                'OFauto008m01012305021250280842',
                'OFauto008m01012305021542510102',
                'OFauto008m01012305021928550397',
                'OFauto008m01012305030954490998',
                'OFauto008m01012305031332130046',
                'OFauto008m01012305031444490645',
                'OFauto008m01012305031705110765',
                'OFauto008m01012305031742490362',
                'OFauto008m01012305041529080699',
                'OFauto008m01012305041553290236',
                'OFauto008m01012305041641410274',
                'OFauto008m01012305041649380052',
                'ONauto193m01012305151501190381',
                'ONauto637m01012305231405040581',
                'ONauto230m01012305241624480714',
                'ONauto230m01012305241624480714',
                'ONauto230m01012306021141310456',
                'ONauto230m01012306021142250309',
                'ONato1388m01012306071226360336',
                'ONauto637m01012306071227150934',
                'OFauto016m01012306141053090343',
                'OFauto016m01012306191954510567',
                'ONauto138m01012306271131070915',
                'OFauto016m01012306281538150561',
                'Kiauto009m01132307041911529987',
                'Kiauto012m01132307041919060437',
                'Kiauto008m01142307041935364237',
                'Kiauto016m01132307041955119749',
                'Kiauto012m01142307042003580623',
                'Kiauto014m01142307051125441129',
                'Kiauto006m01142307051134015649',
                'Kiauto015m01132307051149219281',
                'Kiauto006m01142307051216382648',
                'Kiauto011m01132307051228392436',
                'Kiauto012m01132307051241266736',
                'Kiauto017m01142307051253490403',
                'Kiauto016m01132307051305229558',
                'Kiauto011m01132307051309501758',
                'Kiauto014m01142307051317492370',
                'Kiauto014m01142307051453463864',
                'Kiauto013m01142307051756489819',
                'Kiauto014m01142307051809212071',
                'Kiauto009m01132307051809590405',
                'Kiauto014m01142307051810165540',
                'Kiauto014m01142307051831588688',
                'Kiauto004m01132307051844585640',
                'Kiauto011m01132307051905488773',
                'Kiauto010m01142307051946266449',
                'Kiauto011m01132307051948331615',
                'Kiauto009m01132307051950228183',
                'Kiauto011m01142307052010451362',
                'Kiauto005m01142307070852179764',
                'Kiauto008m01132307070902560895',
                'Kiauto015m01142307070922228069',
                'Kiauto012m01132307070912284573',
                'Kiauto005m01132307070850435915',
                'Kiauto013m01142307070914060619',
                'Kiauto008m01132307070902289985',
                'Kiauto016m01132307070924466630',
                'Kiauto012m01142307070912100323',
                'Kiauto004m01142307070846362623',
                'Kiauto007m01132307070857210899',
                'Kiauto008m01142307070902122031',
                'OFauto016m01012307071601150250',
                'ONauto230m01012307071641570137',
                'ONauto230m01012307071643060216',
                'ONato1389m01012307101100150334',
                'ONauto230m01012307101122400858',
                'ONauto230m01012307101125130133',
                'ONato1388m01012307101134570618',
                'OFauto016m01012307101836440243',
                'ONauto230m01012307111018180165',
                'ONauto230m01012307111019090136',
                'ONato1388m01012307281118040315',
                'ONato1487m01012307282150430682',
                'ONato1487m01012307282204480320',
                'ONato1487m01012307282210410355',
                'ONato1487m01012307290808520842',
                'ONato1487m01012307291328430159',
                'ONato1487m01012307291410370525',
                'ONato1485m01012307291449220551',
                'ONato1487m01012307292242230389',
                'ONato1487m01012307301226570050',
                'ONato1487m01012307301535250499',
                'ONato1487m01012307301550300314',
                'ONato1487m01012307301616430825',
                'ONato1487m01012307301619460510',
                'ONato1487m01012307301733440680',
                'ONato1487m01012307301733440680',
                'ONato1487m01012307301738020506',
                'ONato1487m01012307301845220536',
                'ONato1487m01012307301937100181',
                'ONato1487m01012307301943460451',
                'ONato1487m01012307301947530192',
                'ONato1487m01012307302033220973',
                'ONato1487m01012307310920120793',
                'ONato1487m01012307310945540968',
                'ONato1487m01012307310959200582',
                'ONato1487m01012307311006470226',
                'ONato1487m01012307311112230713',
                'ONato1487m01012307311153520793',
                'ONato1487m01012307311718130858',
                'ONato1487m01012307311901410082',
                'ONato1487m01012307311943360583'
            ]
            )
            ->orderBy('id', 'desc')
            ->get();
        
        $trans = json_decode(json_encode($db_trans), true);
        $trans = $this->setSettleAmount($trans, $dev_settle_type);
        $i=0;
        foreach($db_trans as $tran)
        {
            $tran->brand_settle_amount = $trans[$i]['brand_settle_amount'];
            $tran->dev_settle_amount = $trans[$i]['dev_settle_amount'];
            $tran->dev_realtime_settle_amount = $trans[$i]['dev_realtime_settle_amount'];
            $tran->mcht_settle_amount = $trans[$i]['mcht_settle_amount'];
            $tran->sales0_settle_amount = $trans[$i]['sales0_settle_amount'];
            $tran->sales1_settle_amount = $trans[$i]['sales1_settle_amount'];
            $tran->sales2_settle_amount = $trans[$i]['sales2_settle_amount'];
            $tran->sales3_settle_amount = $trans[$i]['sales3_settle_amount'];
            $tran->sales4_settle_amount = $trans[$i]['sales4_settle_amount'];
            $tran->sales5_settle_amount = $trans[$i]['sales5_settle_amount'];
            $tran->save();
            $i++;
            echo $i."\n";
        }
    }
    static public function test()
    {
        $ist = new TransactionController(new Transaction);
        $ist->_test();
    }
}
