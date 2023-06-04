<?php

namespace App\Http\Controllers\Manager;

use App\Models\Post;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PostRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Post API
 *
 * 공지사항 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
 */
class PostController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $posts;

    public function __construct(Post $posts)
    {
        $this->posts = $posts;
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     *
     * @queryParam search string 검색어(제목)
     */
    public function index(IndexRequest $request)
    {
        $search = $request->input('search', '');
        $query  = $this->posts
            ->where('brand_id', $request->user()->brand_id)
            ->where('title', 'like', "%$search%");

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);

    }

    /**
     * 추가
     *
     * 마스터 이상 가능
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PostRequest $request)
    {
        $data = $request->data();
        $data['writer'] = $request->user()->user_name;
        $res  = $this->posts->create($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->posts->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, $id)
    {
        $data = $request->data();
        $res  = $this->posts->where('id', $id)->update($data);
        return $this->response($res ? 1 : 990);
    }

    /**
     * 단일삭제
     *
     * 마스터 이상 가능
     *
     * @urlParam id integer required 공지사항 PK
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $notice)
    {
        $result = $this->delete($this->posts->where('id', $id));
        return $this->response($result);
    }

    public function uploadImage(Request $request)
    {
        $data = [];
        $imgs = [
            'params'    => ['image'],
            'cols'      => ['url'],
            'folders'   => ['posts'],
            'sizes'     => [ 1980],
        ];
        $data = $this->saveImages($request, $data, $imgs);
        return $this->response(0, $data);
    }
}
