<?php
    namespace App\Http\Controllers\Utils;
    
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\ConnectionException;

    class Comm
    {
        static private function getRequestType($type)
        {
            if($type === 0)
                return 'as-form-post';
            else if($type === 1)
                return 'post';
            else if($type === 2)
                return 'get';
            else if($type === 3)
                return 'body-post';
            else if($type === 4)
                return 'curl-post';
            else
                return 'unknown';
        }

        static private function preTreatment($type, $url, $params, $headers, $rand_num)
        {
            $message = self::getRequestType($type);
            $log = [
                'params'    => $params,
                'headers'   => $headers,
            ];
            Log::info("($rand_num)$message-request: $url", $log);
        }

        static private function afterTreatment($type, $url, $res, $rand_num)
        {
            $message = self::getRequestType($type);
            if($res['code'] < 400)
                Log::info("($rand_num)$message-response: $url", $res);
            else if($res['code'] < 500)
                Log::notice("($rand_num)$message-response: $url", $res);
            else
                Log::warning("($rand_num)$message-response: $url", $res);
        }

        static private function httpSender($type, $url, $params, $headers=[])
        {
            $rand_num = rand(1000, 9999);
            self::preTreatment($type, $url, $params, $headers, $rand_num);
            try
            {
                if($type === 0)
                    $res = Http::asForm()->timeout(20)->post($url, $params);
                else if($type === 1)
                    $res = Http::withHeaders($headers)->timeout(20)->post($url, $params);
                else if($type === 2)
                    $res = Http::withHeaders($headers)->timeout(20)->get($url, $params);
                else if($type === 3)
                    $res = Http::withHeaders($headers)->withBody($params)->timeout(20)->post($url);
                else if($type === 4)
                    $res = Http::withHeaders($headers)->withBody(json_encode($params), 'application/json')->timeout(20)->delete($url);

                $code = $res->status();
                $body = $code < 500 ? $res->json() : $res->body();
                // 200일때 json parse 안될경우
                if($body === null)
                    $body = $res->body();
            }
            catch(ConnectionException $ex)
            {
                $body = $ex->getMessage();
                $code = 408;
            }
            $res = ['body' => $body, 'code'=> $code];
            self::afterTreatment($type, $url, $res, $rand_num);
            return $res;
        }

        static public function asPost($url, $params, $headers=[])
        {
            return self::httpSender(0, $url, $params, $headers);
        }

        static public function post($url, $params, $headers=[])
        {
            return self::httpSender(1, $url, $params, $headers);
        }

        static public function get($url, $params, $headers=[])
        {
            return self::httpSender(2, $url, $params, $headers);
        }
    
        static public function bodyPost($url, $params, $headers=[])
        {
            return self::httpSender(3, $url, $params, $headers);
        }

        static public function destroy($url, $params, $headers=[])
        {
            return self::httpSender(4, $url, $params, $headers);
        }

        static public function curlPost($url, $params, $headers=[])
        {
            $rand_num = rand(1000, 9999);
            self::preTreatment(4, $url, $params, $headers, $rand_num);
            $res = [];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $body = curl_exec($ch);
            $res['body'] = json_decode($body, true);
            if($res['body'] === null)
                $res['body'] = $body;

            $res['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            self::afterTreatment(4, $url, $res, $rand_num);
            return $res;
        }
    }

?>
