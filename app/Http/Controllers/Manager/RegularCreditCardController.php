<?php

namespace App\Http\Controllers\Manager;

use App\Models\RegularCreditCard;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\StoresTrait;

use App\Http\Requests\Manager\BulkRegister\BulkRegularCardRequest;
use App\Http\Requests\Manager\RegularCreditCardRequest;
use App\Http\Requests\Manager\IndexRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Regular Customer Card API
 *
 * 지정 신용 카드 API 입니다.
 */
class RegularCreditCardController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $cards;

    public function __construct(RegularCreditCard $cards)
    {
        $this->cards = $cards;
    }

    /**
     * 카드 목록출력
     *
     * 카드목록을 불러옵니다.
     */
    public function index(Request $request)
    {
        $data = $this->cards->where('mcht_id', $request->user()->id)->get();
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 단골고객 카드를 추가합니다. (10개이상 등록 불가능)
     */
    public function store(RegularCreditCardRequest $request)
    {
        $data = $request->data();
        if($this->cards->where('mcht_id', $data['mcht_id'])->count() < 10)
        {
            $res = $this->cards->create($data);
            return $this->response($res ? 1 : 990, ['id'=>$res->id, 'mcht_id'=>$data['mcht_id']]);    
        }
        else
            return $this->extendResponse(1999, '10개이상 등록할 수 없습니다.');
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $data = $this->cards->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 단골고객 카드정보를 업데이트합니다.
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function update(RegularCreditCardRequest $request, int $id)
    {
        $data = $request->data();
        $res  = $this->cards->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'mcht_id'=>$data['mcht_id']]);
    }

    /**
     * 단일삭제
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 정기등록카드 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $res = $this->cards->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
    
    /**
     * 대량등록
     *
     * 운영자 이상 가능
     */
    public function bulkRegister(BulkRegularCardRequest $request)
    {
        $current = date('Y-m-d H:i:s');
        $datas = $request->data();

        $cards = $datas->map(function ($data) use($current) {
            $data['created_at'] = $current;
            $data['updated_at'] = $current;
            return $data;
        })->toArray();
        $res = $this->manyInsert($this->cards, $cards);
        return $this->response($res ? 1 : 990);
    }
}
