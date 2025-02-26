<?php

namespace App\Http\Controllers\Manager;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\BrandRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * @group Brand API
 *
 * 브랜드 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 본사 이상권한이 요구됩니다.
 */
class BrandController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $brand;
    protected $imgs;

    public function __construct(Brand $brands)
    {
        $this->brands = $brands;
        $this->imgs = [
            'params'    => [
                'logo_file', 'favicon_file', 'passbook_file',
                'contract_file', 'id_file', 'og_file', 'bsin_lic_file',
                'login_file',
            ],
            'cols'  => [
                'logo_img', 'favicon_img', 'passbook_img',
                'contract_img', 'id_img', 'og_img', 'bsin_lic_img',
                'login_img',
            ],
            'folders'   => [
                'logos', 'favicons', 'passbooks',
                'contracts', 'ids', 'ogs', 'e-ids',
                'logins',
            ],
            'sizes'     => [
                96, 32, 500,
                500, 500, 1200, 500,
                2000,
            ],
        ];
    }

    private function isMainBrand($brand_id)
    {
        return $brand_id == env('MAIN_BRAND_ID', 1) ? true : false;
    }
    
    /**
     * 목록출력
     *
     * 브랜드 이상 가능
     *
     * @queryParam search string 검색어(브랜드 명)
     */
    public function index(IndexRequest $request)
    {
        $search     = $request->input('search', '');
        $brand_id   = $request->user()->brand_id;

        if($this->isMainBrand($request->user()->brand_id) && Ablilty::isDevLogin($request))
            $query = $this->brands;
        else
            $query = $this->brands->where('id', $brand_id);

        $query  = $query
            ->where('is_delete', false)
            ->where('name', 'like', "%$search%");

        $data   = $this->getIndexData($request, $query);
        foreach ($data['content'] as $content) {
            $content->free = $content->pv_options->free;
            $content->paid = $content->pv_options->paid;
            $content->auth = $content->pv_options->auth;
            $content->makeHidden(['pv_options']);
        }
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 개발사 이상 가능
     *
     */
    public function store(BrandRequest $request)
    {
        return $this->response(951);
    }

    /**
     * 단일조회
     *
     * 브랜드 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function show(Request $request, int $id)
    {
        if(Ablilty::isBrandCheck($request, $id, true) === false)
            return $this->response(951);
        else
        {
            $data = $this->brands->where('id', $id)
                ->with(['beforeBrandInfos', 'differentSettlementInfos', 'identityAuthInfos'])
                ->first();

            return $this->response($data ? 0 : 1000, $data);
        }
    }

    /**
     * 업데이트
     *
     * 개발사 이상, 또는 로그인한 브랜드 ID와 같은 계정(본인)만 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function update(BrandRequest $request, int $id)
    {
        // 휴대폰 인증
        if(Ablilty::isBrandCheck($request, $id, true) === false)
            return $this->response(951);
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if($request->user()->level > 35)
        {
            $request = $this->setSealFile($request);
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            
            $query  = $this->brands->where('id', $id);
            $brand = $query->first();
            $res = $query->update($data);
            if($res)
            {
                Redis::set($data['dns'], null, 'EX', 1);
                Redis::set("brand-info", null, 'EX', 1);
            }
            return $this->response($res ? 1 : 990, ['id'=>$id]);    
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * 개발사 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function destroy(Request $request, int $id)
    {
        if(Ablilty::isBrandCheck($request, $id) === false)
            return $this->response(951);
        if(EditAbleWorkTime::validate() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        $brand = $this->brands->where('id', $id)->first();
        $res = $this->delete($this->brands->where('id', $id), ['logo_img', 'favicon_img']);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    public function setSealFile($request)
    {
        if($request->hasFile('p2p.seal_file'))
        {
            $img    = $request->file('p2p.seal_file');
            $ext    = $img->extension();                
            $name = time().md5(pathinfo($img, PATHINFO_FILENAME)).".$ext";

            if(env('FILESYSTEM_DISK') === 's3')
                $request->pv_options['p2p']['seal_file'] = $this->ToS3('seals', $img, $name);
            else if(env('FILESYSTEM_DISK') === 'n-cloud')
                $request->pv_options['p2p']['seal_file'] = $this->ToNCloud('seals', $img, $name);
            else if(env('FILESYSTEM_DISK') === 'cloudinary')
                $request->pv_options['p2p']['seal_file'] = $this->ToCloudinary('seals', $img, $name);
            else
                $request->pv_options['p2p']['seal_file'] = $this->ToLocal('seals', $img, $name);
        }
        return $request;
    }
}
