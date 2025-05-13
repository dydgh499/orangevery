<?php
    namespace App\Http\Traits\Util;
    use Illuminate\Support\Facades\Log;

    trait APITrait
    {
        public function response($message, $data=[], $http_code=200)
        {
            $res = ['message'=>$message, 'data'=>$data];

            if($http_code === 200 || $http_code === 201)
                Log::info('api-response', $res);
            else
                Log::warning('api-response', $res);

            return response($res, $http_code);
        }

        public function apiErrorResponse($code, $message, $header_code=409, $temp=[])
        {
            if($code !== "0000")
            {
                if(strpos($message, '('.env('ERROR_PREFIX', "PV")) === false)
                    $message = "($code) $message";
            }

            $body = json_encode([
                'result_cd' => $code,
                'result_msg' => $message,
                'temp' => $temp,
            ], JSON_UNESCAPED_UNICODE);
            return response($body, $header_code);
        }

        public function viewErrorResponse($code, $message, $return_url='')
        {
            if($code !== "0000")
                $message = "($code) $message";
            $params = [
                'return_url' =>  $return_url,
                'result_cd' => $code,
                'result_msg' => $message,
            ];
            return view('fail', ['params'=>$params]);
        }

        public function setAuthExtendParams($params)
        {
            $json = json_decode($params['temp'], true);
            $params['temp'] = isset($json['temp']) ? $json['temp'] : '';
            $params['return_url'] = isset($json['return_url']) ? $json['return_url'] : '';
            $params['buyer_phone'] = isset($json['buyer_phone']) ? $json['buyer_phone'] : '';
            return $params;
        }
    }
