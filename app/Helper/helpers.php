<?php
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Redis;
    use App\Models\Brand;
    use App\Models\Salesforce;

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

    function isOrderator($request)
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
            if($code == 200)
                $body = $res->json();
        }
        catch(ConnectionException $ex)
        {
            $body = $ex->getMessage();
            $code = 500;

            $retry += 1;
            if($retry < 5)
                return httpSender($type,$url, $params, $headers, $retry);
        }
        $res = ['body' => $body, 'code'=> $code];
        $code == 200 ? Log::info('http-response', $res) : Log::error('http-response', $res);
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
        $brand = Redis::get($request->dns);
        if($brand == null)
        {
            $brand = Brand::where('dns', $request->dns)->first();
            if($brand)
            {
                $res = Redis::set($request->dns, json_encode($brand));
                return json_decode(json_encode($brand), true);
            }
            else
            {
                $logs = ['ip'=>$request->ip(), 'method'=>$request->method(),'input'=>$request->all()];
                return null;
            }
        }
        else
            return json_decode($brand, true);
    }

    function globalAuthFilter($query, $request, $parent_table='')
    {
        $parent_table = $parent_table != "" ? $parent_table."." : "";
        if(isMerchandise($request))
        {   // 가맹점
            $col = $parent_table == 'merchandises' ? "id" : 'mcht_id';
            $query = $query->where($parent_table.$col,  $request->user()->id);
        }
        else if(isSalesforce($request))
        {   // 영업자
            $idx = globalLevelByIndex($request->user()->level);            
            $query = $query->where($parent_table."sales".$idx."_id",  $request->user()->id);
        }
        else if(isOrderator($request))
        {   // all

        }
        else
            throw new Exception('알 수 없는 등급');
        return $query;
    }

    function globalPGFilter($query, $request, $parent_table='')
    {
        $parent_table = $parent_table != "" ? $parent_table."." : "";
        if($request->pg_id)
            $query = $query->where($parent_table.'pg_id', $request->pg_id);
        if($request->ps_id)
            $query = $query->where($parent_table.'ps_id', $request->ps_id);
        if($request->settle_type)
            $query = $query->where($parent_table.'settle_type', $request->settle_type);
        if($request->terminal_id)
            $query = $query->where($parent_table.'terminal_id', $request->terminal_id);
        return $query;
    }

    function globalSalesFilter($query, $request, $parent_table='')
    {
        $parent_table = $parent_table != "" ? $parent_table."." : "";
        if($request->sales0_id)
            $query = $query->where($parent_table.'sales0_id', $request->sales0_id);
        if($request->sales1_id)
            $query = $query->where($parent_table.'sales1_id', $request->sales1_id);
        if($request->sales2_id)
            $query = $query->where($parent_table.'sales2_id', $request->sales2_id);
        if($request->sales3_id)
            $query = $query->where($parent_table.'sales3_id', $request->sales3_id);
        if($request->sales4_id)
            $query = $query->where($parent_table.'sales4_id', $request->sales4_id);
        if($request->sales5_id)
            $query = $query->where($parent_table.'sales5_id', $request->sales5_id);
        if($request->custom_id)
            $query = $query->where($parent_table.'custom_id', $request->custom_id);

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
                return 0;
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

    function globalGetSalesByIds($sales_ids)
    {        
        $salesforces = Salesforce::whereIn('id', $sales_ids)->get(['id', 'nick_name', 'settle_tax_type']);
        return globalGetIndexingByCollection($salesforces);
    }

    function globalGetIndexingByCollection($salesforces)
    {        
        $sales_index_by_ids = [];
        foreach ($salesforces as $salesforce) {
            $sales_index_by_ids[$salesforce->id] = $salesforce;
        }
        return $sales_index_by_ids;
    }

    function mappingSalesInfo($content, $sales_index_by_ids, $sales_id, $idx)
    {
        $sales = 'sales'.$idx;
        if(isset($sales_index_by_ids[$sales_id]))
        {
            $content[$sales] = $sales_index_by_ids[$sales_id];
            $content[$sales."_name"] = $sales_index_by_ids[$sales_id]->nick_name;
        }
        else
        {
            $content[$sales] = null;
            $content[$sales."_name"] = '삭제된 영업자';
        }
        return $content;
    }

    function globalMappingSales($sales_index_by_ids, $contents)
    {
        foreach ($contents as $content) {
            $content = mappingSalesInfo($content, $sales_index_by_ids, $content->sales0_id, 0);
            $content = mappingSalesInfo($content, $sales_index_by_ids, $content->sales1_id, 1);
            $content = mappingSalesInfo($content, $sales_index_by_ids, $content->sales2_id, 2);
            $content = mappingSalesInfo($content, $sales_index_by_ids, $content->sales3_id, 3);
            $content = mappingSalesInfo($content, $sales_index_by_ids, $content->sales4_id, 4);
            $content = mappingSalesInfo($content, $sales_index_by_ids, $content->sales5_id, 5);
        }
        return $contents;
    }
