<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\ComagainController;

/**
 * @group Operator API
 *
 * 오퍼레이터 관리 메뉴에서 사용될 API 입니다. 본사 이상권한이 요구됩니다.
 */
class OperatorController extends ComagainController
{
    public function __construct()
    {
        parent::__construct('operators');
    }
}
