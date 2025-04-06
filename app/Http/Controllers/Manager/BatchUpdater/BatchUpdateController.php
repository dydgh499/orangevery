<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;

class BatchUpdateController extends Controller
{
    public function __construct()
    {
        $this->target = '';
    }


    protected function batchResponse($row, $apply_type)
    {
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 적용되었습니다.' : "적용된 ".$apply_type."이 존재하지 않습니다.");
    }

    protected function wrongTypeAccess()
    {
        error([], '잘못된 접근입니다.');
        print_r(json_encode(['code'=>1999, 'message'=>'잘못된 접근입니다.', 'data'=>[]], JSON_UNESCAPED_UNICODE));
        exit;
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

    protected function addLogs($history_type, $after_datas)
    {
        foreach($after_datas as $after_data)
        {
            operLogging(HistoryType::CREATE, $this->target, [], $after_data, $after_data['mcht_name']);
        }
    }

    protected function updateLogs($history_type, $query, $after_data, $update_keys, $title_key)
    {
        $before_datas = (clone $query)->get($update_keys)->toArray();
        foreach($before_datas as $before_data)
        {
            operLogging(HistoryType::UPDATE, $this->target, $before_data, $after_data, $before_data[$title_key]);
        }
    }
}
