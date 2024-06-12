<?php
namespace App\Http\Controllers\Manager\Merchandise;

use App\Models\Merchandise\SpecifiedTimeDisablePayment;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Merchandise\SpecifiedTimeDisablePaymentRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Enums\HistoryType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Specified Time Disable Limit Payment API
 *
 * 지정시간 결제불가 API 입니다.
 */
class SpecifiedTimeDisablePaymentController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $specified_time_disable_payments;

    public function __construct(SpecifiedTimeDisablePayment $specified_time_disable_payments)
    {
        $this->specified_time_disable_payments = $specified_time_disable_payments;
    }
    
    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $data = [];
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(SpecifiedTimeDisablePaymentRequest $request)
    {
        $data = $request->data();
        $res = $this->specified_time_disable_payments->create($data);
        return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);
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
        $data = $this->specified_time_disable_payments->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(SpecifiedTimeDisablePaymentRequest $request, int $id)
    {
        $data = $request->data();
        $res = $this->specified_time_disable_payments->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $res = $this->specified_time_disable_payments->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
