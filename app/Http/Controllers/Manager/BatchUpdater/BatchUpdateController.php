<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\MerchandiseFeeUpdater;
use App\Http\Controllers\Manager\MerchandiseController;
use App\Models\Merchandise;
use App\Models\Merchandise\NotiUrl;
use App\Models\Merchandise\PaymentModule;
use App\Models\Merchandise\MerchandiseColumnApplyBook;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Auth\AuthOperatorIP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchUpdateController extends Controller
{
    protected function batchResponse($row, $apply_type)
    {
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 적용되었습니다.' : "적용된 ".$apply_type."이 존재하지 않습니다.");
    }

    protected function getApplyBookDatas($request, $ids, $dest_key, $cols)
    {
        $datas = [];
        $now = date("Y-m-d H:i:s");
        foreach($ids as $id)
        {
            $datas[] = [
                'brand_id' => $request->user()->brand_id,
                $dest_key => $id,
                'apply_at' => $request->apply_dt,
                'apply_data' => json_encode($cols),
                'change_status' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        return $datas;
    }

    protected function getBatchMode($request)
    {
        $cond_1 = count($request->selected_idxs);
        $cond_2 = ($request->selected_sales_id && $request->selected_level);
        $cond_3 = ($request->selected_all && $request->filter['page_size']);  //전체 변경
        
        if($cond_1 || $cond_2 || $cond_3)
        {
            if($cond_3)
                return 3;
            else
                return 1;
        }
        else
        {
            logging([], '잘못된 접근');
            echo "wrong access";
            return 0;
        }
    }
}
