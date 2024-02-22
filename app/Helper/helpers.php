<?php
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\ConnectionException;
    use App\Models\Brand;
    use App\Models\Salesforce;
    use Carbon\Carbon;
    use App\Http\Controllers\Log\OperatorHistoryContoller;
    use App\Enums\HistoryType;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Redis;

    function getPGType($pg_type)
    {
        $pgs = [
            'paytus', 'koneps', 'aynil', 'welcome', 'hecto', 'lumen',
            'payletter', 'wholebic', 'korpay', 'kppay', 'thepayone', 'ezpg',
            'cmpay', 'kiwoom', 'wizzpay', 'nestpay', 'e2u','addone',
            'saminching','wgp', 'brightfixC3', 'danal', 'baumpns', 
            'passgo', 'buddypay', 'withpay', 'fixpay', 'galaxiamoneytree',
            'bkwinners', 'welcome1', 'toss'
        ];
        return $pgs[$pg_type-1];
    }
    
    function isMainBrand($brand_id)
    {
        return $brand_id == env('MAIN_BRAND_ID', 1) ? true : false;
    }

    function isMerchandise($request)
    {
        return $request->user()->tokenCan(13) == false ? true : false;
    }
    
    function isSalesforce($request)
    {
        $cond_1 = $request->user()->tokenCan(13) == true;
        $cond_2 = $request->user()->tokenCan(35) == false;
        return $cond_1 && $cond_2;
    }

    function isOperator($request)
    {
        return $request->user()->tokenCan(35);
    }

    function httpSender($type, $url, $params, $headers=[], $retry=0)
    {
        $log = [
            'url'   => $url,
            'params' => $params,
            'headers' => $headers,
            'retry' => $retry,
        ];
        Log::info('http-request', $log);
        try
        {
            if($type == 0)
                $res = Http::asForm()->post($url, $params);
            else if($type == 1)
                $res = Http::withHeaders($headers)->timeout(60)->post($url, $params);
            else if($type == 2)
                $res = Http::withHeaders($headers)->timeout(60)->get($url, $params);

            $code = $res->status();
            $body = $code < 500 ? $res->json() : $res->body();
            // 200일때 json parse 안될경우
            if($body == null)
                $body = $res->body();
        }
        catch(ConnectionException $ex)
        {
            $body = $ex->getMessage();
            $code = 500;

            $retry += 1;
            if($retry < 5)
                return httpSender($type, $url, $params, $headers, $retry);
        }
        $res = ['body' => $body, 'code'=> $code];
        if($code < 400)
            Log::info('http-response', $res);
        else if($code < 500)
            Log::notice('http-response', $res);
        else
            Log::warning('http-response', $res);
        return $res;
    }

    function asPost($url, $params, $headers=[])
    {
        return httpSender(0, $url, $params, $headers);
    }

    function post($url, $params, $headers=[])
    {
        return httpSender(1, $url, $params, $headers);
    }

    function get($url, $params, $headers=[])
    {
        return httpSender(2, $url, $params, $headers);
    }

    function getBrandByDNS($request)
    {

        $brand = Brand::where('dns', $request->dns)->with(['beforeBrandInfos'])->first();
        return json_decode($brand, true);
    }
    
    function globalAuthFilter($query, $request, $parent_table='')
    {
        $table = $parent_table != "" ? $parent_table."." : "";
        if(isMerchandise($request))
        {   // 가맹점
            $col = $parent_table == 'merchandises' ? "id" : 'mcht_id';
            $query = $query->where($table.$col,  $request->user()->id);
        }
        else if(isSalesforce($request))
        {   // 영업자
            $idx = globalLevelByIndex($request->user()->level);            
            $query = $query->where($table."sales".$idx."_id",  $request->user()->id);
        }
        else if(isOperator($request))
        {   // all

        }
        else
            throw new Exception('알 수 없는 등급');
        return $query;
    }

    function globalPGFilter($query, $request, $parent_table='')
    {
        $table = $parent_table != "" ? $parent_table."." : "";
        if($request->pg_id)
            $query = $query->where($table.'pg_id', $request->pg_id);
        if($request->ps_id)
            $query = $query->where($table.'ps_id', $request->ps_id);
        if($request->terminal_id)
            $query = $query->where($table.'terminal_id', $request->terminal_id);
        if(zeroCheck($request, 'settle_type'))
            $query = $query->where($table.'settle_type', $request->settle_type);
        if(zeroCheck($request, 'mcht_settle_type'))
            $query = $query->where($table.'mcht_settle_type', $request->mcht_settle_type);
        if(zeroCheck($request, 'module_type'))
            $query = $query->where($table.'module_type', $request->module_type);
        return $query;
    }

    function globalSalesFilter($query, $request, $parent_table='')
    {
        $table = $parent_table != "" ? $parent_table."." : "";
        if($request->sales0_id)
            $query = $query->where($table.'sales0_id', $request->sales0_id);
        if($request->sales1_id)
            $query = $query->where($table.'sales1_id', $request->sales1_id);
        if($request->sales2_id)
            $query = $query->where($table.'sales2_id', $request->sales2_id);
        if($request->sales3_id)
            $query = $query->where($table.'sales3_id', $request->sales3_id);
        if($request->sales4_id)
            $query = $query->where($table.'sales4_id', $request->sales4_id);
        if($request->sales5_id)
            $query = $query->where($table.'sales5_id', $request->sales5_id);
        if($request->custom_id)
            $query = $query->where($table.'custom_id', $request->custom_id);

        return $query;
    }

    function globalLevelByIndex($level)
    {
        switch($level)
        {
            case 10:
                return -1;
            case 13:
                return 0;
            case 15:
                return 1;
            case 17:
                return 2;
            case 20:
                return 3;
            case 25:
                return 4;
            case 30:
                return 5;
            case 40:
                return 6;
            case 50;
                return 6;
            default:
                return "UNKNOWUN";
        }
    }

    function globalGetUniqueIdsBySalesIds($contents)
    {
        return collect($contents)->flatMap(function ($content) {
            return [
                $content->sales0_id, $content->sales1_id, $content->sales2_id,
                $content->sales3_id, $content->sales4_id, $content->sales5_id,
            ];
        })->unique();
    }

    function globalGetSalesByIds($sales_ids, $is_all=true)
    {
        $globalGetIndexingByCollection = function($salesforces) {
            $sales_index_by_ids = [];
            foreach ($salesforces as $salesforce) {
                $sales_index_by_ids[$salesforce->id] = $salesforce;
            }
            return $sales_index_by_ids;
        };

        $query = Salesforce::whereIn('id', $sales_ids);
        if($is_all == false)
            $query = $query->where('is_delete', false);
        $salesforces = $query->get(['id', 'sales_name', 'settle_tax_type']);
        return $globalGetIndexingByCollection($salesforces);
    }

    function globalMappingSales($sales_index_by_ids, $contents)
    {
        $mappingSalesInfo = function($content, $sales_index_by_ids, $sales_id, $idx) {
            $sales = 'sales'.$idx;
            if(isset($sales_index_by_ids[$sales_id]))
            {
                if(isset($sales_index_by_ids[$sales_id]))
                {
                    $content[$sales] = $sales_index_by_ids[$sales_id];
                    $content[$sales."_name"] = $sales_index_by_ids[$sales_id]->sales_name;    
                }
                else
                    $content[$sales."_name"] = '삭제된 영업자';
            }
            else
            {
                $content[$sales] = null;
                $content[$sales."_name"] = '';
            }
            return $content;
        };

        foreach ($contents as $content) {
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales0_id, 0);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales1_id, 1);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales2_id, 2);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales3_id, 3);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales4_id, 4);
            $content = $mappingSalesInfo($content, $sales_index_by_ids, $content->sales5_id, 5);
        }
        return $contents;
    }

    
    function getDefaultUsageChartFormat($data)
    {        
        $division_by_delete = function($item) {
            return $item->is_delete == true;
        };
        $chart = [
            'this_week_add' => 0,
            'this_week_del' => 0,
            'this_month_add' => 0,
            'this_month_del' => 0,
            'total' => $data['total'],
        ];   
        $first_dy_week = Carbon::now()->startOfWeek();
        $first_dy_month = Carbon::now()->startOfMonth();

        if(count($data['content']))
        {
            $this_week = $data['content']->filter(function ($item) use ($first_dy_week) {
                return Carbon::parse($item->created_at)->greaterThanOrEqualTo($first_dy_week);
            })->values();
    
            $this_month = $data['content']->filter(function ($item) use ($first_dy_month) {
                return Carbon::parse($item->created_at)->greaterThanOrEqualTo($first_dy_month);
            })->values();
    
            $chart['this_week_add'] = $this_week->filter(function ($item) use ($division_by_delete) {
                return $division_by_delete($item) == false;
            })->values()->count();
            $chart['this_week_del'] = $this_week->filter(function ($item) use ($division_by_delete) {
                return $division_by_delete($item) == true;
            })->values()->count();
            
            $chart['this_month_add'] = $this_month->filter(function ($item) use ($division_by_delete) {
                return $division_by_delete($item) == false;
            })->values()->count();
        
            $chart['this_month_del'] = $this_month->filter(function ($item) use ($division_by_delete) {
                return $division_by_delete($item) == true;
            })->values()->count();
    
        }
        return $chart;
    }

    function getDefaultTransChartFormat($data, $settle_key)
    {
        $chart = [
            'appr' => [
                'amount' => 0,
                'count' => 0,
                'profit' => 0,
                'trx_amount' => 0,
                'hold_amount' => 0,
                'settle_fee' => 0,
                'total_trx_amount' => 0
            ],
            'cxl' => [
                'amount' => 0,
                'count' => 0,
                'profit' => 0,
                'trx_amount' => 0,
                'hold_amount' => 0,
                'settle_fee' => 0,
                'total_trx_amount' => 0
            ],
            'total' => [
                'amount' => 0,
                'count' => 0,
                'profit' => 0,
                'trx_amount' => 0,
                'hold_amount' => 0,
                'settle_fee' => 0,
                'total_trx_amount' => 0    
            ]
        ];

        // 트랜잭션 유형별로 데이터를 분류하며, 동시에 필요한 합계를 계산합니다.
        foreach ($data as $transaction) {
            $type = $transaction->is_cancel ? 'cxl' : 'appr';
            $chart[$type]['amount'] += $transaction->amount;
            $chart[$type]['count']++;
            $chart[$type]['profit'] += $transaction[$settle_key];
            $chart[$type]['trx_amount'] += $transaction->trx_amount;
            $chart[$type]['hold_amount'] += $transaction->hold_amount;
            $chart[$type]['settle_fee'] += $transaction->mcht_settle_fee;
            $chart[$type]['total_trx_amount'] += $transaction->total_trx_amount;
        }
        // 전체 차트 값을 계산합니다.
        foreach ($chart['appr'] as $key => $value) {
            $chart['total'][$key] = $chart['appr'][$key] + $chart['cxl'][$key];
        }
        return $chart;  
    }

    function logging($data, $msg='test')
    {
        Log::info($msg, $data);
    }

    function error($data, $msg='test')
    {
        Log::error($msg, $data);
    }

    function operLogging(HistoryType $history_type, $history_target, $history_detail, $history_title='', $brand_id='', $oper_id='')
    {
        $cond_1 = $history_type == HistoryType::LOGIN;
        $cond_2 = $history_type != HistoryType::LOGIN && isOperator(request());
        if($cond_1 || $cond_2)
        {
            $request = request()->merge([
                'history_type' => $history_type->value,
                'history_target' => $history_target,
                'history_title'  => $history_title,
                'history_detail' => json_encode($history_detail, JSON_UNESCAPED_UNICODE),
                'brand_id' => $brand_id,
                'oper_id' => $oper_id,
            ]);
            return OperatorHistoryContoller::logging($request);
        }
    }

    function zeroCheck($request, $key)
    {
        // 0 허용, 빈값, null 안됨
        return ($request->input($key, 0) || $request->input($key, '') == 0) && !is_null($request->input($key, null));
    }
