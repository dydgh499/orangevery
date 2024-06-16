<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Service\AbnormalConnectionHistory;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function popup()
    {

    }
}
