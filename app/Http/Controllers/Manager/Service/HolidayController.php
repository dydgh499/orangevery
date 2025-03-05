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

    public function filterAlreadyExistHoliday($holidays, $brand_id, $this_year)
    {
        $datas  = [];
        $cur_time   = date('Y-m-d H:i:s');
        $exist_holidays = $this->holidays
            ->where('brand_id', $brand_id)
            ->where('rest_dt', '>=', $this_year."-01-01")
            ->get()
            ->toArray();

        foreach($holidays as $holiday)
        {
            $idx = array_search($holiday['rest_dt'], array_column($exist_holidays, 'rest_dt'));
            if($idx === false)
            {   // 이미 등록되지 않을 경우에만
                $holiday['brand_id'] = $brand_id;
                $holiday['created_at'] = $cur_time;
                $holiday['updated_at'] = $cur_time;
                $datas[] = $holiday;
            }
        }
        return $datas;
    }

    /*
     * 공휴일 자동업데이트
     */
    public function updateHoliday()
    {
        $this_year  = (int)Carbon::now()->format('Y');
        $holidays   = array_merge(
                $this->getOneYearHolidays($this_year), 
                $this->getOneYearHolidays($this_year + 1)
        );

        $brand_ids  = Brand::where('is_delete', false)->pluck('id')->all();
        foreach($brand_ids as $brand_id)
        {
            $datas = $this->filterAlreadyExistHoliday($holidays, $brand_id, $this_year);
            if(count($datas))
            {
                logging($datas, 'update-holiday-brand');
                $res = $this->manyInsert($this->holidays, $datas);    
            }
        }
        return $this->response(1);
    }

    /*
     * 공휴일 대량 업데이트
     */
    public function register(Request $request)
    {
        $this_year  = (int)Carbon::now()->format('Y');
        $holidays   = array_merge(
                $this->getOneYearHolidays($this_year), 
                $this->getOneYearHolidays($this_year + 1)
        );
        $datas = $this->filterAlreadyExistHoliday($holidays, $request->user()->brand_id, $this_year);
        if(count($datas))
            $res = $this->manyInsert($this->holidays, $datas);
        return $this->response(1);
    }

    /**
     * 목록출력
     *
     * 브랜드 이상 가능
     *
     * @queryParam search string 검색어(공휴일 명)
     */
    public function index(Request $request)
    {
        $brand_id   = $request->user()->brand_id;
        $s_dt = Carbon::now()->subYears(1)->format("Y-01-01");
        $e_dt = Carbon::now()->addYears(1)->format("Y-12-31");
        $holidays  = $this->holidays
            ->where('brand_id', $brand_id)
            ->where('rest_dt', '>=', $s_dt)
            ->where('rest_dt', '<=', $e_dt)
            ->get();
        return $this->response(0, $holidays);
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
