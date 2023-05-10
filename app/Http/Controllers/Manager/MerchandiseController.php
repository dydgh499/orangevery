<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\ComagainController;

/**
 * @group Merchandise API
 *
 * 가맹점 관리 메뉴에서 사용될 API 입니다. 가맹점 이상권한이 요구됩니다.
 */
class MerchandiseController extends ComagainController
{
    public function __construct()
    {
        parent::__construct('merchandises');
    }
}
