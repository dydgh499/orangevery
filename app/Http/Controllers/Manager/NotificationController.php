<?php

namespace App\Http\Controllers\Manager;

use App\Models\Notification;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\NotificationForm;
use App\Http\Requests\Manager\IndexForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Notification API
 *
 * 푸쉬알림 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class NotificationController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $notifications;

    public function __construct(Notification $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->notifications
            ->where('brand_id', $request->user()->brand_id)
            ->where('title', 'like', "%$search%");

        $data   = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * @bodyParam noti_type string required 알림 타입(0=사용 안함, 1=바로발송, 5=예약발송, 10=스케줄링)
     * @bodyParam title string required 알림 제목
     * @bodyParam content string required 알림 내용
     * @bodyParam redirect_url string required 이동할 URL
     * @bodyParam noti_s_dttm string required 알림시작일(datetime) Example: 2022-12-19 10:25:35
     * @bodyParam noti_days string required 알림날짜, 일요일~토요일(0~6)을 숫자로 표기합니다. Example: [0,3,6]
     * @return \Illuminate\Http\Response
     */
    public function store(NotificationForm $request)
    {
        $data = $request->data();
        $result = $this->notifications->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 알림 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->notifications->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @bodyParam noti_type string required 알림 타입(0=사용 안함, 1=바로발송, 5=예약발송, 10=스케줄링)
     * @bodyParam title string required 알림 제목
     * @bodyParam content string required 알림 내용
     * @bodyParam redirect_url string required 이동할 URL
     * @bodyParam noti_s_dttm string required 알림시작일(datetime) Example: 2022-12-19 10:25:35
     * @bodyParam noti_days string required 알림날짜, 일요일~토요일(0~6)을 숫자로 표기합니다. Example: [0,3,6]
     * @return \Illuminate\Http\Response
     */
    public function update(NotificationForm $request, $id)
    {
        $data = $request->data();
        $result = $this->notifications->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 알림 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->notifications->where('id', $id), []);
        return $this->response($result);
    }
}
