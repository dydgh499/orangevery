<?php

namespace App\Http\Controllers\Log;

use App\Models\Log\OperatorHistory;

use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Operator-History API
 *
 * 운영자 활동이력 API 입니다.
 */
class OperatorHistoryContoller extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $operator_histories;

    public function __construct(OperatorHistory $operator_histories)
    {
        // 가맹점, 결제모듈, 영업점, 구분정보, 수수료율, 매출, 금융 VAN
        $this->operator_histories = $operator_histories;
    }
    
    /**
     * 목록출력
     *
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $cols = [
            'operators.nick_name',
            'operator_histories.*',
        ];
        $query  = $this->operator_histories
            ->join('operators', 'operator_histories.oper_id', '=', 'operators.id')
            ->where('operator_histories.brand_id', $request->user()->brand_id);
        if($search != '')
        {
            $query = $query->where(function ($query) use ($search) {
                return $query->where('operators.nick_name', 'like', "%$search%")
                    ->orWhere('operator_histories.history_title', 'like', "%$search%");
            });
        }

        $data = $this->getIndexData($request, $query, 'operator_histories.id', $this->operator_histories->cols, 'operator_histories.created_at');
        return $this->response(0, $data);
    }

    public static function logging(Request $request)
    {
        $instance = new OperatorHistoryContoller(new OperatorHistory);
        return $instance->store($request);
    }
    /**
     * 추가
     *
     * 운영자 이상 가능
     *
     * @bodyParam user_pw string 유저 패스워드
     */
    public function store(Request $request)
    {
        $data = [
            'brand_id' => $request->brand_id ? $request->brand_id : $request->user()->brand_id,
            'oper_id' => $request->oper_id ? $request->oper_id : $request->user()->id,
            'history_type' => $request->history_type,
            'history_title' => $request->history_title,
            'history_target' => $request->history_target,
            'history_detail' => $request->history_detail,
        ];
        return $this->operator_histories->create($data);
    }

    /**
     * 단일조회(상세조회)
     *
     * 본사 이상 가능
     *
     * @urlParam id integer required 영업자 이력 PK
     */
    public function show($id)
    {
        $data = $this->operator_histories->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 이상거래 PK
     */
    public function destroy($id)
    {
        //
    }
}
