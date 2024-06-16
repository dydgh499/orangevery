<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\AbnormalConnectionHistory;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise;
use App\Models\Salesforce;
use App\Models\Operator;
use App\Models\Log\OperatorHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Enums\HistoryType;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Requests\Manager\IndexRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @group abnormal connection histories API
 *
 * 이상 접속 이력 API 입니다.
 */
class AbnormalConnectionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $abnormal_connections;

    public function __construct(AbnormalConnectionHistory $abnormal_connections)
    {
        $this->abnormal_connections = $abnormal_connections;
    }

    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = $this->abnormal_connections
            ->where('brand_id', $request->user()->brand_id)
            ->where(function ($query) use ($search) {
                return $query->where('request_ip', 'like', "%$search%")
                    ->orWhere('target_key', 'like', "%$search%")
                    ->orWhere('target_value', 'like', "%$search%");
            });
        if($request->connection_type !== null)
            $query = $query->where('connection_type', $request->connection_type);

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    private function registrationByTimePeriod($query, $request, $type)
    {
        if($type > 0)
            $query = $query->whereColumn('updated_at', '!=', 'password_change_at');

        return $query
            ->where('brand_id', $request->user()->brand_id)            
            ->select([
                DB::raw("$type as type"),
                DB::raw('
                COALESCE(SUM(CASE 
                    WHEN updated_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 12 HOUR 
                        AND updated_at < DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 18 HOUR 
                    THEN 1 ELSE 0 END), 0) AS "yesterday_afternoon",
                COALESCE(SUM(CASE 
                    WHEN updated_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) + INTERVAL 18 HOUR 
                        AND updated_at < CURDATE() 
                    THEN 1 ELSE 0 END), 0) AS "yesterday_evening",
                COALESCE(SUM(CASE 
                    WHEN updated_at >= CURDATE() 
                        AND updated_at < CURDATE() + INTERVAL 6 HOUR 
                    THEN 1 ELSE 0 END), 0) AS "today_last_night",
                COALESCE(SUM(CASE 
                    WHEN updated_at >= CURDATE() + INTERVAL 6 HOUR 
                        AND updated_at < CURDATE() + INTERVAL 12 HOUR 
                    THEN 1 ELSE 0 END), 0) AS "today_noon"
            ')]);
    }

    public function secureReport(Request $request)
    {
        $current_at = Carbon::now()->format('Y-m-d H:i:s');
        $abnormal_s_at = Carbon::now()->subDay(2)->format('Y-m-d 00:00:00');
        $abnormal_connections = $this->abnormal_connections
            ->where('brand_id', $request->user()->brand_id)
            ->where('created_at', '>=',  $abnormal_s_at)
            ->orderby('created_at', 'desc')
            ->get();

        // 시간대별 가맹점 작업 현황
        $work_status_s_at = Carbon::now()->subDay(1)->format('Y-m-d 12:00:00');
        $work_status_by_timezone = $this->registrationByTimePeriod(new PaymentModule, $request, 0)
            ->unionAll($this->registrationByTimePeriod(new Merchandise, $request, 1))
            ->unionAll($this->registrationByTimePeriod(new Salesforce, $request, 2))
            ->unionAll($this->registrationByTimePeriod(new Operator, $request, 3))
            ->get();

        // 운영자 로그인 현황
        $login_histories = OperatorHistory::join('operators', 'operator_histories.oper_id', '=', 'operators.id')
            ->where('operator_histories.brand_id', $request->user()->brand_id)
            ->where('operator_histories.history_type', HistoryType::LOGIN->value)
            ->where('operator_histories.created_at', '>=', $abnormal_s_at)
            ->orderby('created_at', 'desc')
            ->get([
                'operators.profile_img',
                'operators.nick_name',
                'operators.level',
                'operator_histories.created_at',
            ]);
        return $this->response(0, [
            'current_at' => $current_at,
            'abnormal_s_at' => $abnormal_s_at,
            'work_status_s_at' => $work_status_s_at,

            'abnormal_connections' => $abnormal_connections,
            'work_status_by_timezone' => $work_status_by_timezone,
            'login_histories' => $login_histories,
        ]);
    }

    //시간대별 등록 상세내용
    public function detailWorkStatus(Request $request)
    {
        if((int)$request->detail_time_type === 0)
        {
            $s_dt = Carbon::now()->subDay(1)->format('Y-m-d 12:00:00');
            $e_dt = Carbon::now()->subDay(1)->format('Y-m-d 18:00:00');
        }
        else if((int)$request->detail_time_type === 1)
        {
            $s_dt = Carbon::now()->subDay(1)->format('Y-m-d 18:00:00');
            $e_dt = Carbon::now()->format('Y-m-d 00:00:00');
        }
        else if((int)$request->detail_time_type === 2)
        {
            $s_dt = Carbon::now()->format('Y-m-d 00:00:00');
            $e_dt = Carbon::now()->format('Y-m-d 06:00:00');
        }
        else if((int)$request->detail_time_type === 3)
        {
            $s_dt = Carbon::now()->format('Y-m-d 06:00:00');
            $e_dt = Carbon::now()->format('Y-m-d 12:00:00');
        }
        else
            return $this->response(951);

        $activity_types = [HistoryType::CREATE->value, HistoryType::UPDATE->value, HistoryType::DELETE->value];
        $histories = OperatorHistory::join('operators', 'operator_histories.oper_id', '=', 'operators.id')
            ->where('operator_histories.brand_id', $request->user()->brand_id)
            ->whereIn('operator_histories.history_type', $activity_types)
            ->where('operator_histories.created_at', '>=', $s_dt)
            ->where('operator_histories.created_at', '<=', $e_dt)
            ->where('operator_histories.history_target', ['가맹점', '결제모듈', '영업점', '운영자'])
            ->orderby('operator_histories.created_at', 'desc')
            ->get([
                'operators.profile_img',
                'operators.nick_name',
                'operators.level',
                'operator_histories.id',
                'operator_histories.created_at',
                'operator_histories.history_type',
                'operator_histories.history_title',
                'operator_histories.history_target',
            ]);
        return $this->response(0, $histories);
    }
}
