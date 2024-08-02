<?php

namespace App\Http\Controllers\Manager\Service;

use App\Models\Brand;
use App\Models\Service\Holiday;

use App\Http\Controllers\Utils\Comm;
use App\Http\Requests\Manager\Service\HolidayRequest;
use App\Http\Requests\Manager\IndexRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use SimpleXMLElement;

class HolidayController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait;
    protected $holidays;

    public function __construct(Holiday $holidays)
    {
        $this->holidays = $holidays;       
    }

    private function getHolidayFromOpenAPI($item)
    {
        return [
            'rest_dt' => Carbon::createFromFormat('Ymd', $item['locdate'])->format('Y-m-d'),
            'rest_name' => $item['dateName'],
            'rest_type' => (int)$item['dateKind'],
        ];
    }

    private function getOneYearHolidays($year)
    {
        $holidays = [];
        $url = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';
        for ($month=1; $month < 13; $month++) 
        {
            $params = [
                'ServiceKey' => urldecode(env('OPEN_API_ENC_KEY')),
                'solYear'	 => $year,
                'solMonth'	=> sprintf("%02d", $month),
            ];
            $res = Comm::get($url, $params);            
            $xml = new SimpleXMLElement($res['body']);
            $xml = json_decode(json_encode($xml), true);

            if($xml['header']['resultCode'] === "00")
            {
                if(isset($xml['body']['items']) && count($xml['body']['items']))
                {
                    $items = $xml['body']['items'];
                    if(isset($items['item']['isHoliday']))
                    {
                        $item = $items['item'];
                        if($item['isHoliday'] === "Y")
                            $holidays[] = $this->getHolidayFromOpenAPI($item);
                    }                    
                    else
                    {
                        foreach($items['item'] as $item)
                        {
                            if($item['isHoliday'] === "Y")
                                $holidays[] = $this->getHolidayFromOpenAPI($item);        
                        }        
                    }
                }
            }
            else
                error(['message'=>$xml], "holiday parse fail");
        }
        return $holidays;
    }

    /*
     * 공휴일 자동등록 (매년 12월 30일 내년꺼 등록)
     */
    public function updateNextHolidaysAllBrands()
    {
        $datas      = [];
        $cur_time   = date('Y-m-d H:i:s');
        $next_year  = (int)Carbon::now()->format('Y') + 1;
        $holidays   = $this->getOneYearHolidays($next_year);
        $brand_ids  = Brand::where('is_delete', false)->pluck('id')->all();
        foreach($holidays as $holiday)
        {
            foreach($brand_ids as $brand_id)
            {
                $data = $holiday;
                $data['brand_id'] = $brand_id;
                $data['created_at'] = $cur_time;
                $data['updated_at'] = $cur_time;
                $datas[] = $data;
            }
        }

        $res = $this->manyInsert($this->holidays, $datas);
        return $this->response($res ? 1 : 990);
    }

    /*
     * 공휴일 대량 업데이트
     */
    public function updateHolidays(Request $request)
    {
        $is_already_parse = $this->holidays
            ->where('brand_id', $request->user()->brand_id)
            ->where('rest_dt', date('Y')."-01-01")
            ->exists();
        if($is_already_parse)
            return $this->extendResponse(1999, '이미 대량으로 읽어온 공휴일이 존재합니다.');
        else
        {
            $datas      = [];
            $cur_time   = date('Y-m-d H:i:s');
            $holidays   = $this->getOneYearHolidays(date('Y'));
            foreach($holidays as $holiday)
            {
                $data = $holiday;
                $data['brand_id']   = $request->user()->brand_id;
                $data['created_at'] = $cur_time;
                $data['updated_at'] = $cur_time;
                $datas[] = $data;
            }
    
            $res = $this->manyInsert($this->holidays, $datas);
            return $this->response($res ? 1 : 990);
        }
    }

    /**
     * 목록출력
     *
     * 브랜드 이상 가능
     *
     * @queryParam search string 검색어(공휴일 명)
     */
    public function index(IndexRequest $request)
    {
        $search     = $request->input('search', '');
        $brand_id   = $request->user()->brand_id;
        $query  = $this->holidays->where('brand_id', $brand_id)->where('rest_name', 'like', "%$search%");
        $data   = $this->getIndexData($request, $query, 'id', [], 'rest_dt');
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 개발사 이상 가능
     */
    public function store(HolidayRequest $request)
    {
        if($request->user()->tokenCan(35))
        {
            $res = $this->holidays->create($request->data());
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
    public function show(Request $request, int $id)
    {
        $data = $this->holidays->where('id', $id)->first();
        return $this->response($data ? 0 : 1000, $data);
    }

    /**
     * 업데이트
     *
     * @urlParam id integer required 브랜드 PK
     */
    public function update(HolidayRequest $request, int $id)
    {
        $res = $this->holidays->where('id', $id)->update($request->data());
        return $this->response($res ? 1 : 990, ['id'=>$id]);
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
        $res = $this->holidays->where('id', $id)->delete();
        return $this->response($res ? 1 : 990, ['id'=>$id]);
    }
}
