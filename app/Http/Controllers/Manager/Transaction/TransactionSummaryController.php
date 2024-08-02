<?php
namespace App\Http\Controllers\Manager\Transaction;

use App\Models\Merchandise;
use App\Models\Transaction;
use App\Models\Salesforce;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Manager\Transaction\TransactionFilter;

use App\Http\Controllers\Utils\ChartFormat;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionSummaryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $transactions;
    
    public function __construct(Transaction $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * 차트 데이터 출력
     *
     * 가맹점 이상 가능
     */
    public function chart(IndexRequest $request)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);

        $query = TransactionFilter::common($request);
        $cols = TransactionFilter::getTotalCols($target_settle_amount);
        $chart = $query->first($cols);
        $chart = ChartFormat::transaction($chart);
        return $this->response(0, $chart);
    }
    
    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        [$target_id, $target_settle_id, $target_settle_amount] = getTargetInfo($request->level);
        $cols = [
            "transactions.$target_id as user_id",
            DB::raw("SUM($target_settle_amount) as total_profit"),
            DB::raw("SUM(IF(is_cancel = 0, amount, 0)) AS total_appr_amount"),
            DB::raw("SUM(IF(is_cancel = 1, amount, 0)) AS total_cxl_amount"),
            DB::raw("SUM(is_cancel = 0) AS total_appr_count"),
            DB::raw("SUM(is_cancel = 1) AS total_cxl_count"),
        ];
        if((int)$request->level === 10)
            $cols[] = 'merchandises.mcht_name as user_name';

        $query = TransactionFilter::common($request)->groupBy("transactions.$target_id");
        $data = TransactionFilter::pagenation($request, $query, $cols, "transactions.$target_id", true);

        if((int)$request->level !== 10)
        {
            $sales_ids = $data['content']->pluck('user_id')->all();
            $salesforces = globalGetSalesByIds($sales_ids);
            foreach($data['content'] as $content)
            {
                $content->makeHidden(['total_trx_amount', 'trx_amount', 'trx_dttm', 'hold_amount', 'cxl_dttm']);
                if(isset($salesforces[$content->user_id]))
                    $content->user_name = $salesforces[$content->user_id]->sales_name;
                else
                    $content->user_name = '삭제된 영업자';
            }
        }
        return $this->response(0, $data);
    }
}
