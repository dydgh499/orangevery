<?php
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Redis;
    use App\Models\Brand;

    function isMainBrand($brand_id)
    {
        return $brand_id == env('MAIN_BRAND_ID', 1) ? true : false;
    }

    function isMerchandise($request)
    {
        return $request->user()->tokenCan(15) == false ? true : false;
    }

    function isMbrOnlyMcht($request)
    {
        return $request->user()->tokenCan('mbr_only_mcht') ? true : false;
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
                return $brand;
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
