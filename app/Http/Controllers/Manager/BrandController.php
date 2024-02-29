<?php

namespace App\Http\Controllers\Manager;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Transaction;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\Manager\BrandRequest;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

/**
 * @group Brand API
 *
 * 브랜드 관리 메뉴에서 사용될 API 입니다. 조회를 제외하고 마스터 이상권한이 요구됩니다.
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
                'contracts', 'ids', 'ogs', 'bsin_lic',
                'logins',
            ],
            'sizes'     => [
                96, 32, 500,
                500, 500, 1200, 500,
                2000,
            ],
        ];
    }

    /**
     * 차트 데이터 출력
     *
     * 브랜드 이상 가능
     */
    public function chart(Request $request)
    {
        $chart = $this->brands->first([
            DB::raw("(SUM(deposit_amount) + SUM(extra_deposit_amount)) AS total_deposit_amount"),
            DB::raw("SUM(deposit_amount) AS deposit_amount"),
            DB::raw("SUM(extra_deposit_amount) AS extra_deposit_amount"),
            DB::raw("SUM(curr_deposit_amount) AS curr_deposit_amount"),
        ]);
        $total_dev_amount = (int)$this->getTotalDevFee($request);
        logging(['total_dev_amount'=>$total_dev_amount]);
        $chart->extra_deposit_amount += $total_dev_amount;
        $chart->total_deposit_amount += $total_dev_amount;
        return $this->response(0, $chart);
    }

    public function getTotalDevFee($request)
    {
        if(isMainBrand($request->user()->brand_id) && $request->user()->tokenCan(50))
        {
            $s_dt = Carbon::now()->copy()->subMonthNoOverflow(1)->startOfMonth()->format('Y-m-d');
            $e_dt = Carbon::now()->copy()->subMonthNoOverflow(1)->endOfMonth()->format('Y-m-d');

            $sum = Transaction::join('brands', 'transactions.brand_id', '=', 'brands.id')
                ->where(function($query) use($s_dt, $e_dt) {
                    $query->where(function($query) use($s_dt, $e_dt) {
                        $query->where('transactions.is_cancel', false)
                            ->whereRaw("concat(trx_dt, ' ', trx_tm) >= ?", [$s_dt])
                            ->whereRaw("concat(trx_dt, ' ', trx_tm) <= ?", [$e_dt]);
                    })->orWhere(function($query) use($s_dt, $e_dt) {
                        $query->where('transactions.is_cancel', true)
                            ->whereRaw("concat(cxl_dt, ' ', cxl_tm) >= ?", [$s_dt])
                            ->whereRaw("concat(cxl_dt, ' ', cxl_tm) <= ?", [$e_dt]);
                    });
                })
                ->first([DB::raw('SUM(dev_realtime_settle_amount + dev_settle_amount) as dev_realtime_settle_amount')]);
            return $sum->dev_realtime_settle_amount;
        }
        else
            return 0;

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

        if(isMainBrand($request->user()->brand_id) && $request->user()->tokenCan(50))
            $query = $this->brands;
        else
            $query = $this->brands->where('id', $brand_id);

        $query  = $query
            ->where('is_delete', false)
            ->where('name', 'like', "%$search%")
            ->with(['devAmount']);

        $data   = $this->getIndexData($request, $query);
        foreach ($data['content'] as $content) {
            $content->free = $content->pv_options->free;
            $content->paid = $content->pv_options->paid;
            $content->auth = $content->pv_options->auth;
            if(count($content->devAmount))
                $content->extra_deposit_amount += (int)$content->devAmount[0]->dev_percent_amount;

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
        if($request->user()->tokenCan(35))
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $res = $this->brands->create($data);
            return $this->response($res ? 1 : 990, ['id'=>$res->id]);
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * 브랜드 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function show(Request $request, $id)
    {
        $data = $this->brands->where('id', $id)
            ->with(['beforeBrandInfos', 'differentSettlementInfos'])
            ->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * 개발사 이상, 또는 로그인한 브랜드 ID와 같은 계정(본인)만 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function update(BrandRequest $request, $id)
    {
        $data = $request->data();
        $data = $this->saveImages($request, $data, $this->imgs);
        
        $query  = $this->brands->where('id', $id);
        $res = $query->update($data);

        Redis::set($data['dns'], json_encode($query->with(['beforeBrandInfos'])->first()));
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }

    /**
     * 단일삭제
     *
     * 개발사 이상 가능
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function destroy(Request $request, $id)
    {
        $brand = $this->brands->where('id', $id)->first();
        $res = $this->delete($this->brands->where('id', $id), ['logo_img', 'favicon_img']);
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
