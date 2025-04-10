<?php

namespace App\Http\Controllers\Manager\BatchUpdater;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateController;
use App\Http\Controllers\Manager\Merchandise\NotiUrlController;

use App\Models\Merchandise\NotiUrlColumnApplyBook;
use App\Models\Merchandise\NotiUrl;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkNotiUrlRequest;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Noti-Url-Batch-Updater API
 *
 * 노티주소 일괄 업데이트 group 입니다.
 */
class BatchUpdateNotiUrlController extends BatchUpdateController
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $noti_urls;

    public function __construct(NotiUrl $noti_urls)
    {
        $this->noti_urls = $noti_urls;
        $this->target = '노티 URL';
    }

    private function getApplyRow($request, $cols)
    {
        $query = $this->notiUrlBatch($request);
        if($request->apply_type === 0)
            $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $cols, 'note');
        else
        {
            $datas = $this->getApplyBookDatas($request, $query->pluck('id')->all(), 'noti_id', $cols);
            $result = app(ActivityHistoryInterface::class)->book($this->target, $query, $cols, 'note', new NotiUrlColumnApplyBook, $datas);
            $row = $result ? count($datas) : 0;
        }
        return $row;
    }

    /**
     * 선택된 노티주소 가져오기
     */
    private function notiUrlBatch($request)
    {
        $apply_mode = $this->getBatchMode($request);
        if($apply_mode === 3)
        {
            $filter_request = new Request();
            $filter_request->merge($request->filter);
            $filter_request->setUserResolver(function () use ($request) {
                return $request->user();
            });
            $query = resolve(NotiUrlController::class)->commonSelect($filter_request);

            if($request->total_selected_count !== (clone $query)->count())
            {
                print_r(json_encode(['code'=>1999, 'message'=>'변경할 개수와 조회 개수가 같지 않습니다.', 'data'=>[]], JSON_UNESCAPED_UNICODE));
                exit;
            }
            return $query;
        }
        else if($apply_mode === 1)
        {
            return $this->noti_urls
                ->where('brand_id', $request->user()->brand_id)
                ->whereIn('id', $request->selected_idxs);
        }
        else
            return null;
    }

    public function setSendUrl(Request $request)	
    {
        $cols = ['send_url' => $request->send_url];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '노티주소');

    }
    
    public function setNotiStatus(Request $request)	
    {
        $cols = ['noti_status' => $request->noti_status];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '노티주소');
    }
    
    public function setNote(Request $request)	
    {
        $cols = ['note' => $request->note];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '노티주소');
    }

    public function setSendType(Request $request)	
    {
        $cols = ['send_type' => $request->send_type];
        $row = $this->getApplyRow($request, $cols);
        return $this->batchResponse($row, '노티주소');
    }

    /**
     * 일괄삭제
     */
    public function batchRemove(Request $request)
    {
        $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'note');
        return $this->extendResponse($row ? 1: 990, $row ? $row.'개가 삭제되었습니다.' : '삭제된 노티주소가 존재하지 않습니다.');
    }

    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function register(BulkNotiUrlRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $brand_id = $request->user()->brand_id;
        $datas = $request->data();

        $noti_urls = $datas->map(function ($data) use($request, $current, $brand_id) {
            $data['brand_id']   = $brand_id;
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        
        $ids = app(ActivityHistoryInterface::class)->batchAdd($this->target, $this->noti_urls, $noti_urls, 'note', $current, $brand_id);
        return $this->response(1, $ids);
    }
}
