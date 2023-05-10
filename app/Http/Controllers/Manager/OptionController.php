<?php

namespace App\Http\Controllers\Manager;

use App\Models\Option;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\OptionForm;
use App\Http\Requests\Manager\IndexForm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Option API
 *
 * 상품 관리 메뉴에서 사용될 옵션 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class OptionController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $options;

    public function __construct(Option $options)
    {
        $this->options = $options;
        $this->imgs = [
            'params'    => ['options'],
            'folders'   => ['options'],
            'sizes'     => [256],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(옵션명)
     */
    public function index(IndexForm $request)
    {
        $search = $request->input('search', '');
        $query  = $this->options
            ->join('products', 'options.prod_id', '=', 'products.id')
            ->join('categorys', 'products.cate_id', '=', 'categorys.id')
            ->where('categorys.brand_id', $request->user()->brand_id)
            ->where('options.name', 'like', "%$search%");

        $data = $this->getIndexData($request, $query, 'options.id', ['options.*', 'products.name as product_name'], 'options.created_at');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * @bodyParam prod_id integer required 상위 상품 ID
     * @bodyParam group_id integer required 옵션 그룹 ID
     * @bodyParam name string required 옵션명
     * @bodyParam price string required 옵션가격
     * @bodyParam option_img file 옵션 이미지(max-width: 256px, 이상은 리사이징)
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OptionForm $request)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->options->create($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * @urlParam id integer required 옵션 ID
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->options->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id int 옵션 ID
     * @bodyParam prod_id integer required 상위 상품 ID
     * @bodyParam group_id integer required 옵션 그룹 ID
     * @bodyParam name string required 옵션명
     * @bodyParam price string required 옵션가격
     * @bodyParam option_img file 옵션 이미지(max-width: 256px, 이상은 리사이징)
     * @return \Illuminate\Http\Response
     */
    public function update(OptionForm $request, $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        $result = $this->options->where('id', $id)->update($data);
        return $this->response($result ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 옵션 ID
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = $this->delete($this->options->where('id', $id), ['option_img']);
        return $this->response($result);
    }
}
