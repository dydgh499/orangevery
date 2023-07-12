<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\NotiSendHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

class NotiSendHistoryController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $noti_send_histories;

    public function __construct(NotiSendHistory $noti_send_histories)
    {
        $this->noti_send_histories = $noti_send_histories;
    }

    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->noti_send_histories
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where('send_url', 'like', "%$search%");

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    public function detail(Requset $request, $id)
    {

    }
}
