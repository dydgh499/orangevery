<?php
    namespace App\Http\Traits\Util;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Http\Client\ConnectionException;

    trait HttpTrait
    {
        protected function getRequestType($type)
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
                return 'delete';
            else if($type === 5)
                return 'curl-post';
            else
                return 'unknown';
        }

        protected function preTreatment($type, $url, $params, $headers, $rand_num)
        {
            $message = $this->getRequestType($type);
            $log = [
                'params'    => $params,
                'headers'   => $headers,
            ];
            Log::info("($rand_num)$message-request: $url", $log);
        }

        protected function afterTreatment($type, $url, $res, $rand_num)
        {
            $message = $this->getRequestType($type);
            if($res['code'] < 400)
                Log::info("($rand_num)$message-response: $url", $res);
            else if($res['code'] < 500)
                Log::notice("($rand_num)$message-response: $url", $res);
            else
                Log::warning("($rand_num)$message-response: $url", $res);
        }

        public function httpSender($type, $url, $params, $headers=[])
        {
            $rand_num = rand(1000, 9999);
            $this->preTreatment($type, $url, $params, $headers, $rand_num);
            try
            {
                $http  = Http::withOptions([
                    'verify' => env('APP_ENV') === 'local' ? false : true,
                ]);
                if($type === 0)
                    $res = $http->asForm()->timeout(30)->post($url, $params);
                else if($type === 1)
                    $res = $http->withHeaders($headers)->timeout(30)->post($url, $params);
                else if($type === 2)
                    $res = $http->withHeaders($headers)->timeout(30)->get($url, $params);
                else if($type === 3)
                    $res = $http->withHeaders($headers)->withBody($params)->timeout(30)->post($url);
                else if($type === 4)
                    $res = $http->withHeaders($headers)->withBody(json_encode($params), 'application/json')->timeout(20)->delete($url);

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
            $this->afterTreatment($type, $url, $res, $rand_num);
            return $res;
        }

        public function asPost($url, $params, $headers=[])
        {
            return $this->httpSender(0, $url, $params, $headers);
        }

        public function post($url, $params, $headers=[])
        {
            return $this->httpSender(1, $url, $params, $headers);
        }

        public function bodyPost($url, $params, $headers=[])
        {
            return $this->httpSender(3, $url, $params, $headers);
        }

        public function destroy($url, $params, $headers=[])
        {
            return $this->httpSender(4, $url, $params, $headers);
        }

        public function curlPost($url, $params, $headers=[])
        {
            $rand_num = rand(1000, 9999);
            $this->preTreatment(5, $url, $params, $headers, $rand_num);
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
            $this->afterTreatment(5, $url, $res, $rand_num);
            return $res;
        }
    }

?>
