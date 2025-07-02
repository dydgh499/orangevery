<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\ActivityHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Ablilty\ActivityHistoryViewer;

use App\Http\Controllers\Controller;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Http\Request;

/**
 * @group Activity-History API
 *
 * 활동이력 API 입니다.
 */
class ActivityHistoryContoller extends Controller
{
    use ManagerTrait, ExtendResponseTrait, EncryptDataTrait;
    protected $activity_histories;

    public function __construct(ActivityHistory $activity_histories)
    {
        // 가맹점, 결제모듈, 영업라인, 구분정보, 수수료율, 매출, 금융 VAN, GMID
        $this->activity_histories = $activity_histories;
    }

    /**
     * 목록출력
     */
    public function index(IndexRequest $request)
    {
        $page       = $request->input('page');
        $page_size  = $request->input('page_size');
        $s_page     = ($page - 1) * $page_size;
        $oper_cols  = array_merge(ActivityHistoryViewer::getSelectCols(), [
            "operators.nick_name",
            'operators.profile_img',
            'operators.level',
        ]);
        $oper_query = ActivityHistoryViewer::operatorSelect($request)
                ->groupBy('activity_histories.user_id')
                ->select($oper_cols);
        $query = $oper_query;
        if ((clone $query)->count()) 
        {
            $total = (clone $query)->get('user_id')->count();
            $content = $query
                ->orderBy('activity_e_at', 'desc')
                ->offset($s_page)
                ->limit($page_size)
                ->get();
        }
        else
        {
            $total = 0;
            $content = [];
        }
        $data = [
                'total' => $total,
                'content' => $content,
            ];
        return $this->response(0, $data);
    }

    /**
     * 유저 상세이력
     */
    public function detail(Request $request, $id)
    {
        $content = ActivityHistoryViewer::getDetailSelect($request);
        foreach($content as $data)
        {
            $data->before_history_detail = ActivityHistoryViewer::paidOptionVisiable($request, $data->before_history_detail);
            $data->after_history_detail  = ActivityHistoryViewer::paidOptionVisiable($request, $data->after_history_detail);
        }
        return $this->response(0, $content);
    }

    /**
     * 타겟 상세이력
     */
    public function target(Request $request, $target_id)
    {
        $request->validate(['history_target' => 'required']);
            
        $content = ActivityHistoryViewer::getDetailSelect($request);
        foreach($content as $data)
        {
            $data->before_history_detail = ActivityHistoryViewer::paidOptionVisiable($request, $data->before_history_detail);
            $data->after_history_detail  = ActivityHistoryViewer::paidOptionVisiable($request, $data->after_history_detail);
            $data = ActivityHistoryViewer::userVisiable($request, $data);
        }
        return $this->response(0, $content);
    }
}
