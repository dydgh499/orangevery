<?php

namespace App\Http\Controllers\Manager;

use App\Models\Post;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\PostRequest;
use App\Http\Requests\Manager\IndexRequest;
use Carbon\Carbon;

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
            ->where('is_delete', false)
            ->where('title', 'like', "%$search%")
            ->whereNull('parent_id')
            ->with(['replies']);

        if($request->type)
            $query = $query->where('type', $request->type);

        $data = $this->getIndexData($request, $query, 'id', $this->posts->cols, 'updated_at');
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
        $u_res = true;
        $data = $request->data();

        $data['writer'] = $request->user()->user_name;
        $data['level'] = $request->user()->level;
        $i_res = $this->posts->create($data);

        if($data['parent_id'] != null)
        {
            $u_res = $this->posts
                ->where('id', $data['parent_id'])
                ->update(['is_reply' => true]);
        }
        return $this->response($i_res && $u_res ? 1 : 990);
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

    /**
     * 이미지 단일 업로드
     *
     * 게시글에 사용할 이미지를 등록합니다.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $data = [];
        $imgs = [
            'params'    => ['file'],
            'cols'      => ['url'],
            'folders'   => ['posts'],
            'sizes'     => [ 1980],
        ];
        $data = $this->saveImages($request, $data, $imgs);
        return $this->response(0, $data);
    }
    
    /**
     * 최근 게시글 불러오기
     *
     * 업데이트 기준 3일이내 게시글들의 제목과 id를 불러옵니다. (최대 5개)
     *
     * @return \Illuminate\Http\Response
     */
    public function recent(Request $request)
    {
        $request->merge([
            'page' => 1,
            'page_size' => 5,
        ]);
        $cur = Carbon::now()->subDays(3)->format('Y-m-d H:i:s');
        $query  = $this->posts
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_reply', false)
            ->where('type', 2)
            ->where('level', '<', 35)
            ->where('is_delete', false)
            ->where('updated_at', '>', $cur);

        $data = $this->getIndexData($request, $query, 'id', $this->posts->cols, 'updated_at');
        return $this->response(0, $data);
    }
}
