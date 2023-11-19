<?php

return [
    "labels" => [
        "search" => "검색",
        "base_url" => "기본 URL",
    ],

    "auth" => [
        "none" => "이 API는 인증이 필요하지 않습니다.",
        "instruction" => [
            "query" => <<<TEXT
                요청을 인증하려면 요청에 쿼리 매개변수 **`:parameterName`**을 포함하세요.
                TEXT,
            "body" => <<<TEXT
                요청을 인증하려면 요청 body에 **`:parameterName`** 매개변수를 포함하세요.
                TEXT,
            "query_or_body" => <<<TEXT
                요청을 인증하려면 쿼리 매개변수나 요청 body에 **`:parameterName`** 매개변수를 포함하세요.
                TEXT,
            "bearer" => <<<TEXT
                요청을 인증하려면 **`"Bearer :placeholder"`** 값과 함께 **`Authorization`** 헤더를 포함하세요.
                TEXT,
            "basic" => <<<TEXT
                요청을 인증하려면 **`"Basic {credentials}"`** 형식으로 **`Authorization`** 헤더를 포함하세요.
                `{credentials}` 값은 사용자 이름/ID와 비밀번호가 콜론(:)으로 결합되어야 합니다.
                이후 base64로 인코딩됩니다.
                TEXT,
            "header" => <<<TEXT
                요청을 인증하려면 **`":placeholder"`** 값과 함께 **`:parameterName`** 헤더를 포함합니다.
                TEXT,
        ],
        "details" => <<<TEXT
            인증이 요구되는 모든 엔드포인트에는 아래 문서에 <small class="badge badge-darkred">인증 필요</small> 배지가 표시되어 있습니다.
            TEXT,
    ],

    "headings" => [
        "introduction" => "소개",
        "auth" => "인증 필요",
    ],

    "endpoint" => [
        "request" => "요청",
        "headers" => "헤더",
        "url_parameters" => "URL Parameters",
        "body_parameters" => "Body Parameters",
        "query_parameters" => "Query Parameters",
        "response" => "응답",
        "response_fields" => "응답 필드",
        "example_request" => "예시 요청",
        "example_response" => "예시 응답",
        "responses" => [
            "binary" => "Binary data",
            "empty" => "Empty response",
        ],
    ],

    "try_it_out" => [
        "open" => "시도하기 ⚡",
        "cancel" => "취소 🛑",
        "send" => "요청 💥",
        "loading" => "⏱ 요청중...",
        "received_response" => "받은 응답",
        "request_failed" => "오류로 인해 요청이 실패했습니다.",
        "error_help" => <<<TEXT
            팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
            해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
            디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.
            TEXT,
    ],

    "links" => [
        "postman" => "View Postman collection",
        "openapi" => "View OpenAPI spec",
    ],
];
