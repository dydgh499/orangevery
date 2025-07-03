<?php
namespace App\Http\Controllers\Manager\Pay;

use App\Models\Pay\PaymentModule;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;
use App\Http\Controllers\Ablilty\BrandInfo;


use App\Http\Requests\Manager\Pay\PayModuleRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;
use App\Http\Controllers\Ablilty\ActivityHistoryInterface;


/**
 * @group Payment Module API
 *
 * 결제모듈 API 입니다.
 */
class PaymentModuleController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $pay_modules;
    protected $target;

    public function __construct(PaymentModule $pay_modules)
    {
        $this->pay_modules = $pay_modules;
        $this->target = '결제모듈';
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(mid, tid)
     * @queryParam module_type integer 모듈타입(0,1,2,3,4,5)
     */
    public function index(IndexRequest $request)
    {
        $query = $this->pay_modules->where('is_delete', false);
        $query = brandFilter($query, $request);
        return $this->response(0, $query->get());
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(PayModuleRequest $request)
    {
        // 같은 브랜드에서 똑같은 값이 존재할 떄
        $isDuplicateId = function($bid, $key, $value) {
            return $this->pay_modules
                ->where('brand_id', $bid)
                ->where('is_delete', 0)
                ->where($key, $value)
                ->exists();
        };
        $data = $request->data();
        $data['oper_id'] = $request->user()->id;
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($data['tid'] !== '' && $isDuplicateId($data['brand_id'], 'tid', $data['tid']))
            return $this->extendResponse(2000, '이미 존재하는 TID 입니다.',['tid'=>$data['tid']]);
        if($data['mid'] !== '' && $isDuplicateId($data['brand_id'], 'mid', $data['mid']))
            return $this->extendResponse(2000, '이미 존재하는 MID 입니다.',['mid'=>$data['mid']]);

        $res = app(ActivityHistoryInterface::class)->add($this->target, $this->pay_modules, $data, 'note');
        if($res)
        {
            return $this->response(1, ['id' => $res->id]);
        }
        else
            return $this->response(990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->pay_modules->where('id', $id)->first();
        if($data)
        {
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            if(Ablilty::isOperator($request))
            {
                return $this->response(0, $data);
            }
            else
                return $this->response(951);
        }
        else
            return $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(PayModuleRequest $request, int $id)
    {
        // 같은 브랜드에서 똑같은 값이 존재할 떄
        $isDuplicateId = function($bid, $pid, $key, $value) {
            return $this->pay_modules
                ->where('brand_id', $bid)
                ->where('id', '!=', $pid)
                ->where('is_delete', 0)
                ->where($key, $value)
                ->exists();
        };  // tid 중복해서 사용하는 브랜드들은 ..?
        $query = $this->pay_modules->where('id', $id);
        $module = $query->first();
        $data = $request->data();

        if(Ablilty::isBrandCheck($request, $module->brand_id) === false)
            return $this->response(951);
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isOperator($request))
        {
            $brand = BrandInfo::getBrandById($request->user()->brand_id);
            if($brand['ov_options']['free']['use_tid_duplicate'] && $data['tid'] != '' && $isDuplicateId($data['brand_id'], $id, 'tid', $data['tid']))
                return $this->extendResponse(2000, '이미 존재하는 TID 입니다.',['mid' => $data['tid']]);
            if($brand['ov_options']['free']['use_mid_duplicate'] && $data['mid'] != '' && $isDuplicateId($data['brand_id'], $id, 'mid', $data['mid']))
                return $this->extendResponse(2000, '이미 존재하는 MID 입니다.',['mid' => $data['mid']]);
            
            $row = app(ActivityHistoryInterface::class)->update($this->target, $query, $data, 'note');
            if($row)
                return $this->response(1, ['id' => $id]);
            else
                return $this->response(990);

        }
        else
            return $this->response(951);
        
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $query = $this->pay_modules->where('id', $id);
        $data = $query->first();
        if($data)
        {
            if(Ablilty::isBrandCheck($request, $data->brand_id) === false)
                return $this->response(951);
            if(EditAbleWorkTime::validate() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if(Ablilty::isOperator($request))
            {
                $row = app(ActivityHistoryInterface::class)->destory($this->target, $query, 'note');
                return $this->response(1, ['id' => $id]);
            }
            else
                return $this->response(951);
        }
        else
            return $this->response(1000);
    }

    /**
     * 전체목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(유저 ID)
     */
    public function all(Request $request)
    {
        if(Ablilty::isOperator($request) === false)
            return $this->response(951);
        else
        {
            $request->merge([
                'page' => 1,
                'page_size' => 999999,
            ]);
            $data = $this->pay_modules
                ->where('brand_id', $request->user()->brand_id)
                ->where('is_delete', false)
                ->get();            
            return $this->response(0, $data);    
        }
    }
    
}
