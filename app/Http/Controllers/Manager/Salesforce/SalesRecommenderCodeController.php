<?php

namespace App\Http\Controllers\Manager\Salesforce;

use App\Models\Salesforce\SalesRecommenderCode;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;

use App\Http\Requests\Manager\Salesforce\SalesRecommenderRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalesRecommenderCodeController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $sales_recommender_codes;

    public function __construct(SalesRecommenderCode $sales_recommender_codes)
    {
        $this->sales_recommender_codes = $sales_recommender_codes;
        $this->imgs = [];
    }
    
    public function publishCode($recommend_code, $length)
    {
        return $recommend_code.strtoupper(Str::random($length - strlen($recommend_code)));
    }

    public function publish($recommend_code, $length=10)
    {
        do {
            $recommend_code = self::publishCode($recommend_code, $length);
        }
        while($this->sales_recommender_codes->where('recommend_code', $recommend_code)->exists());
        return $recommend_code;
    }

    /**
     * 목록출력
     *
     * 대리점 이상 가능
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
    public function store(SalesRecommenderRequest $request)
    {
        $data = $request->data();
        $data['recommend_code'] = $this->publish('');
        $res = $this->sales_recommender_codes->create($data);
        return $this->response($res ? 1 : 990, [
            'id' => $res->id, 
            'recommend_code' => $data['recommend_code']
        ]);
    }

    /**
     * 단일조회
     *
     * 영업라인인 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        $data = $this->sales_recommender_codes->where('id', $id)->first();
        return $data ? $this->response(0, $data) : $this->response(1000);
    }

    /**
     * 업데이트
     *
     * 영업라인 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(SalesRecommenderRequest $request, int $id)
    {
        $data = $request->data();
        $res = $this->sales_recommender_codes->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990, ['id'=>$id, 'sales_id'=>$data['sales_id']]);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $res = $this->sales_recommender_codes->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
