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

    public function index()
    {

    }

    public function popup()
    {

    }
}
