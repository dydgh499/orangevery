<?php
    namespace App\Http\Traits\Util;

    trait RealtimeV1Trait
    {
        public function response($message, $data, $http_code)
        {
            $body = [];
            $body['message'] = $message;
            $body['data']    = $data;
            return response($body, $http_code);
        }

        function is_cookie_safe($token)
        {
            if(!isset($token) || empty($token))
                return false;
            else
            {
                $user_info  = $this->dehashing($token);
                $is_safe = isset($user_info['data']['ID']) ? true : false;
            }
            return $is_safe;
        }

        function dehashing($token)
        {
            $parted = explode('%', base64_decode($token));      // 토큰 만들때의 구분자 . 으로 나누기
            if(count($parted) > 1)
            {
                $signature = $parted[2];
                // 위에서 토큰 만들때와 같은 방식으로 시그니처 만들고 비교
                if(hash('sha256', $parted[0].$parted[1]) === $signature)
                    $isHashing = true;
                else
                {
                    $isHashing = false;
                    return [];
                }
                $payload = json_decode($parted[1], true);
                return $payload;
            }
            else
                return [];
        }
    }
?>
