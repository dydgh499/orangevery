<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>PV BF Mobile API</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/theme-comagain.style.css" media="screen">
    <link rel="stylesheet" href="/theme-comagain.style.css" media="print">
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .javascript-example code { display: none; }
                    body .content .php-example code { display: none; }
                    body .content .python-example code { display: none; }
                    body .content .bash-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "https://team.payvery.kr";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="../docs/js/tryitout-4.25.0.js"></script>

    <script src="../docs/js/theme-default-4.25.0.js"></script>

</head>

<body data-languages="[&quot;javascript&quot;,&quot;php&quot;,&quot;python&quot;,&quot;bash&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="../docs/images/navbar.png" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
        <div style="display: inline-flex; align-items: center; text-align: center; vertical-align: middle; padding: 1em; justify-content: left;">
        <img src="/logo.svg" alt="logo" class="logo" width="20%"/>
        <span style='margin-left: 0.5em; font-size: 1.7em;'>PAYVERY</span>
    </div>
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                            <button type="button" class="lang-button" data-language-name="php">php</button>
                                            <button type="button" class="lang-button" data-language-name="python">python</button>
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="검색">
    </div>

    <div id="toc">
                    <ul id="tocify-header-" class="tocify-header">
                <li class="tocify-item level-1" data-unique="">
                    <a href="#">소개</a>
                </li>
                            </ul>
                    <ul id="tocify-header-" class="tocify-header">
                <li class="tocify-item level-1" data-unique="">
                    <a href="#">인증 필요</a>
                </li>
                            </ul>
                    <ul id="tocify-header-bf-mobile-api" class="tocify-header">
                <li class="tocify-item level-1" data-unique="bf-mobile-api">
                    <a href="#bf-mobile-api">BF Mobile API</a>
                </li>
                                    <ul id="tocify-subheader-bf-mobile-api" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="bf-mobile-api-POSTapi-v1-bf-sign-in">
                                <a href="#bf-mobile-api-POSTapi-v1-bf-sign-in">로그인</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bf-mobile-api-GETapi-v1-bf-pay-modules">
                                <a href="#bf-mobile-api-GETapi-v1-bf-pay-modules">결제모듈 정보 조회</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bf-mobile-api-GETapi-v1-bf-withdraws-balance">
                                <a href="#bf-mobile-api-GETapi-v1-bf-withdraws-balance">출금가능금액 조회</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bf-mobile-api-POSTapi-v1-bf-withdraws">
                                <a href="#bf-mobile-api-POSTapi-v1-bf-withdraws">출금요청</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="bf-mobile-api-POSTapi-v1-bf-pay-hand">
                                <a href="#bf-mobile-api-POSTapi-v1-bf-pay-hand">수기결제</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                                        <li><a>Documentation powered by Payvery ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>마지막 업데이트:  2023-11-20 15:31:47</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="">소개</h1>
<p>본 문서는 PAYVERY와 BF간 인터페이스에 대하여 기술합니다.</p>
<aside>
    <strong>기본 URL</strong>: <code>https://team.payvery.kr</code>
</aside>
<h2 id="response-format">응답코드 정의</h1>
<p>API 요청의 성공/실패 유무는 HTTP status code로 판별합니다.</p>
<p>Status code (200, 201, 204)인 경우에만 정상 응답이며, 이외의 상태코드의 값은 정상응답이 아닌 것으로 판단합니다.</p>
<table>
    <thead>
        <tr>
            <th>Status Code</th>
            <th>Response Type</th>
            <th>Response Body</th>
        </tr>
        <tr>
            <td>200</td>
            <td>조회 성공</td>
            <td>존재</td>
        </tr>
        <tr>
            <td>201</td>
            <td>추가 및 수정 성공</td>
            <td>미존재</td>
        </tr>
        <tr>
            <td>204</td>
            <td>삭제 성공</td>
            <td>미존재</td>
        </tr>
    </thead>
</table>
<h2 id="response-error">에러코드 표</h2>
<table>
    <thead>
        <tr>
            <th>Status Code</th>
            <th>Code</th>
            <th>Message</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>401</td>
            <td>950</td>
            <td>Authentication token is missing or incorrect</td>
            <td>인증 토큰 누락</td>
        </tr>
        <tr>
            <td>403</td>
            <td>951</td>
            <td>You do not have permission</td>
            <td>권한 인증 실패</td>
        </tr>
        <tr>
            <td>419</td>
            <td>953</td>
            <td>CSRF token mismatch</td>
            <td>CSRF 토큰 누락</td>
        </tr>
        <tr>
            <td>500</td>
            <td>990 ~ 999</td>
            <td>오류 상세 메세지</td>
            <td>시스템 에러 발생</td>
        </tr>
        <tr>
            <td>409</td>
            <td>1000 ~ 1999</td>
            <td>오류 상세 메세지</td>
            <td>비즈니스 로직 처리 에러</td>
        </tr>
    </tbody>
</table>

        <h1 id="">인증 필요</h1>
<p>요청을 인증하려면 <strong><code>"Bearer {ACCESS_TOKEN}"</code></strong> 값과 함께 <strong><code>Authorization</code></strong> 헤더를 포함하세요.</p>
<p>인증이 요구되는 모든 엔드포인트에는 아래 문서에 <small class="badge badge-darkred">인증 필요</small> 배지가 표시되어 있습니다.</p>

        <h1 id="bf-mobile-api">BF Mobile API</h1>

    <p>BF Mobile과 PAYVERY간 API 입니다.</p>

                                <h2 id="bf-mobile-api-POSTapi-v1-bf-sign-in">로그인</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-bf-sign-in">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://team.payvery.kr/api/v1/bf/sign-in"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "brand_id": 12,
    "user_name": "test0001",
    "user_pw": "test0001"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;post(
    'https://team.payvery.kr/api/v1/bf/sign-in',
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'brand_id' =&gt; 12.0,
            'user_name' =&gt; 'test0001',
            'user_pw' =&gt; 'test0001',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://team.payvery.kr/api/v1/bf/sign-in'
payload = {
    "brand_id": 12,
    "user_name": "test0001",
    "user_pw": "test0001"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://team.payvery.kr/api/v1/bf/sign-in" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"brand_id\": 12,
    \"user_name\": \"test0001\",
    \"user_pw\": \"test0001\"
}"
</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-bf-sign-in">
            <blockquote>
            <p>예시 응답 (200):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;access_token&quot;: &quot;9658|zoJ9RPe65DDaIor9jPapdpctuALtWkvMjGFrWn7a034f9c9f&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 12,
        &quot;user_name&quot;: &quot;test0001&quot;,
        &quot;level&quot;: 10
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-bf-sign-in" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-POSTapi-v1-bf-sign-in"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-bf-sign-in"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-bf-sign-in" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-bf-sign-in">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-POSTapi-v1-bf-sign-in" data-method="POST"
      data-path="api/v1/bf/sign-in"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-bf-sign-in', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-bf-sign-in"
                    onclick="tryItOut('POSTapi-v1-bf-sign-in');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-bf-sign-in"
                    onclick="cancelTryOut('POSTapi-v1-bf-sign-in');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-bf-sign-in"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/bf/sign-in</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-bf-sign-in"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-bf-sign-in"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>brand_id</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="brand_id"                data-endpoint="POSTapi-v1-bf-sign-in"
               value="12"
               data-component="body">
    <br>
<p>법인코드를 의미하며 TYINT: 12, MNWORKS: 14가 요구됩니다. <br>예시: <code>12</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_name"                data-endpoint="POSTapi-v1-bf-sign-in"
               value="test0001"
               data-component="body">
    <br>
<p>유저 ID. <br>예시: <code>test0001</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_pw</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_pw"                data-endpoint="POSTapi-v1-bf-sign-in"
               value="test0001"
               data-component="body">
    <br>
<p>패스워드. <br>예시: <code>test0001</code></p>
        </div>
        </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>access_token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>Bearer 토큰 값</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
<br>
<p>유저정보</p>
        </div>
                        <h2 id="bf-mobile-api-GETapi-v1-bf-pay-modules">결제모듈 정보 조회</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>결제모듈 정보를 불러옵니다.<br>한도 및 수기결제에 필요한 데이터들을 조회합니다.</p>

<span id="example-requests-GETapi-v1-bf-pay-modules">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://team.payvery.kr/api/v1/bf/pay-modules"
);

const headers = {
    "Authorization": "Bearer {ACCESS_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;get(
    'https://team.payvery.kr/api/v1/bf/pay-modules',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://team.payvery.kr/api/v1/bf/pay-modules'
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://team.payvery.kr/api/v1/bf/pay-modules" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-bf-pay-modules">
            <blockquote>
            <p>예시 응답 (200):</p>
        </blockquote>
                <pre>

<code class="language-json">[
    {
        &quot;id&quot;: 1,
        &quot;is_old_auth&quot;: 1,
        &quot;module_type&quot;: 1,
        &quot;installment&quot;: 12,
        &quot;pay_year_limit&quot;: 1,
        &quot;pay_month_limit&quot;: 2,
        &quot;pay_day_limit&quot;: 0,
        &quot;pay_single_limit&quot;: 3
    },
    {
        &quot;id&quot;: 5,
        &quot;is_old_auth&quot;: 0,
        &quot;module_type&quot;: 1,
        &quot;installment&quot;: 12,
        &quot;pay_year_limit&quot;: 1,
        &quot;pay_month_limit&quot;: 2,
        &quot;pay_day_limit&quot;: 0,
        &quot;pay_single_limit&quot;: 3
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-bf-pay-modules" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-GETapi-v1-bf-pay-modules"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-bf-pay-modules"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-bf-pay-modules" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-bf-pay-modules">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-GETapi-v1-bf-pay-modules" data-method="GET"
      data-path="api/v1/bf/pay-modules"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-bf-pay-modules', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-bf-pay-modules"
                    onclick="tryItOut('GETapi-v1-bf-pay-modules');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-bf-pay-modules"
                    onclick="cancelTryOut('GETapi-v1-bf-pay-modules');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-bf-pay-modules"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/bf/pay-modules</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-bf-pay-modules"
               value="Bearer {ACCESS_TOKEN}"
               data-component="header">
    <br>
<p><br>예시: <code>Bearer {ACCESS_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-bf-pay-modules"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-bf-pay-modules"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                        </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>결제모듈 고유번호</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_old_auth</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>비인증, 구인증 여부(비인증=0, 구인증=1)</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>installment</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>할부한도(0~12)</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_year_limit</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>연결제 한도(만 단위)</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_month_limit</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>월결제 한도(만 단위)</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_single_limit</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>일결제 한도(만 단위)</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_year_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>연결제 금액</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_month_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>월결제 금액</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_day_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>일결제 금액</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pay_able_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>결제 가능금액(연,월,일,결제한도가 지정되지 않은 경우 null로 반환합니다.)</p>
        </div>
                        <h2 id="bf-mobile-api-GETapi-v1-bf-withdraws-balance">출금가능금액 조회</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>출금가능한금액을 조회합니다.</p>

<span id="example-requests-GETapi-v1-bf-withdraws-balance">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://team.payvery.kr/api/v1/bf/withdraws/balance"
);

const headers = {
    "Authorization": "Bearer {ACCESS_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;get(
    'https://team.payvery.kr/api/v1/bf/withdraws/balance',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://team.payvery.kr/api/v1/bf/withdraws/balance'
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://team.payvery.kr/api/v1/bf/withdraws/balance" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-bf-withdraws-balance">
            <blockquote>
            <p>예시 응답 (200):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;profit&quot;: 412320
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-bf-withdraws-balance" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-GETapi-v1-bf-withdraws-balance"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-bf-withdraws-balance"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-bf-withdraws-balance" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-bf-withdraws-balance">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-GETapi-v1-bf-withdraws-balance" data-method="GET"
      data-path="api/v1/bf/withdraws/balance"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-bf-withdraws-balance', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-bf-withdraws-balance"
                    onclick="tryItOut('GETapi-v1-bf-withdraws-balance');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-bf-withdraws-balance"
                    onclick="cancelTryOut('GETapi-v1-bf-withdraws-balance');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-bf-withdraws-balance"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/bf/withdraws/balance</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-bf-withdraws-balance"
               value="Bearer {ACCESS_TOKEN}"
               data-component="header">
    <br>
<p><br>예시: <code>Bearer {ACCESS_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-bf-withdraws-balance"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-bf-withdraws-balance"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                        </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>profit</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>출금가능한도</p>
        </div>
                        <h2 id="bf-mobile-api-POSTapi-v1-bf-withdraws">출금요청</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>출금가능한금액을 조회합니다.</p>

<span id="example-requests-POSTapi-v1-bf-withdraws">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://team.payvery.kr/api/v1/bf/withdraws"
);

const headers = {
    "Authorization": "Bearer {ACCESS_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "withdraw_amount": 1000
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;post(
    'https://team.payvery.kr/api/v1/bf/withdraws',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'withdraw_amount' =&gt; 1000.0,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://team.payvery.kr/api/v1/bf/withdraws'
payload = {
    "withdraw_amount": 1000
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://team.payvery.kr/api/v1/bf/withdraws" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"withdraw_amount\": 1000
}"
</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-bf-withdraws">
            <blockquote>
            <p>예시 응답 (201):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;id&quot;: 123
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-bf-withdraws" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-POSTapi-v1-bf-withdraws"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-bf-withdraws"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-bf-withdraws" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-bf-withdraws">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-POSTapi-v1-bf-withdraws" data-method="POST"
      data-path="api/v1/bf/withdraws"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-bf-withdraws', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-bf-withdraws"
                    onclick="tryItOut('POSTapi-v1-bf-withdraws');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-bf-withdraws"
                    onclick="cancelTryOut('POSTapi-v1-bf-withdraws');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-bf-withdraws"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/bf/withdraws</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-bf-withdraws"
               value="Bearer {ACCESS_TOKEN}"
               data-component="header">
    <br>
<p><br>예시: <code>Bearer {ACCESS_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-bf-withdraws"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-bf-withdraws"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>withdraw_amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="withdraw_amount"                data-endpoint="POSTapi-v1-bf-withdraws"
               value="1000"
               data-component="body">
    <br>
<p>출금요청할 금액.<br>출금가능금액을 초과할 수 없습니다. <br>예시: <code>1000</code></p>
        </div>
        </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>출금요청 고유번호</p>
        </div>
                        <h2 id="bf-mobile-api-POSTapi-v1-bf-pay-hand">수기결제</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>수기결제 API 입니다.</p>

<span id="example-requests-POSTapi-v1-bf-pay-hand">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://team.payvery.kr/api/v1/bf/pay/hand"
);

const headers = {
    "Authorization": "Bearer {ACCESS_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "pmod_id": 1023,
    "yymm": 2311,
    "card_num": "1234000000005678",
    "buyer_name": "홍길동",
    "buyer_phone": "01000000000",
    "installment": 0,
    "amount": 10000,
    "ord_num": "1700385517624H102302",
    "item_name": "메가커피 아메리카노 L",
    "auth_num": "901212",
    "card_pw": "34"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;post(
    'https://team.payvery.kr/api/v1/bf/pay/hand',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'pmod_id' =&gt; 1023.0,
            'yymm' =&gt; 2311.0,
            'card_num' =&gt; '1234000000005678',
            'buyer_name' =&gt; '홍길동',
            'buyer_phone' =&gt; '01000000000',
            'installment' =&gt; 0.0,
            'amount' =&gt; 10000,
            'ord_num' =&gt; '1700385517624H102302',
            'item_name' =&gt; '메가커피 아메리카노 L',
            'auth_num' =&gt; '901212',
            'card_pw' =&gt; '34',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://team.payvery.kr/api/v1/bf/pay/hand'
payload = {
    "pmod_id": 1023,
    "yymm": 2311,
    "card_num": "1234000000005678",
    "buyer_name": "홍길동",
    "buyer_phone": "01000000000",
    "installment": 0,
    "amount": 10000,
    "ord_num": "1700385517624H102302",
    "item_name": "메가커피 아메리카노 L",
    "auth_num": "901212",
    "card_pw": "34"
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://team.payvery.kr/api/v1/bf/pay/hand" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"pmod_id\": 1023,
    \"yymm\": 2311,
    \"card_num\": \"1234000000005678\",
    \"buyer_name\": \"홍길동\",
    \"buyer_phone\": \"01000000000\",
    \"installment\": 0,
    \"amount\": 10000,
    \"ord_num\": \"1700385517624H102302\",
    \"item_name\": \"메가커피 아메리카노 L\",
    \"auth_num\": \"901212\",
    \"card_pw\": \"34\"
}"
</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-bf-pay-hand">
            <blockquote>
            <p>예시 응답 (201):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;mid&quot;: &quot;wel000707m&quot;,
    &quot;tid&quot;: &quot;90387&quot;,
    &quot;amount&quot;: 100,
    &quot;ord_num&quot;: &quot;1704385517624H102402&quot;,
    &quot;appr_num&quot;: &quot;57480451&quot;,
    &quot;item_name&quot;: &quot;메가커피 아메리카노 L&quot;,
    &quot;pg_id&quot;: 112,
    &quot;trx_id&quot;: &quot;202311200495962&quot;,
    &quot;acquirer&quot;: &quot;IBK&quot;,
    &quot;issuer&quot;: &quot;IBK&quot;,
    &quot;card_num&quot;: &quot;414003******3964&quot;,
    &quot;installment&quot;: &quot;00&quot;,
    &quot;buyer_name&quot;: &quot;홍길동&quot;,
    &quot;trx_dttm&quot;: &quot;2023-11-20 00:09:39&quot;,
    &quot;method&quot;: &quot;수기&quot;,
    &quot;is_cancel&quot;: 0
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-bf-pay-hand" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-POSTapi-v1-bf-pay-hand"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-bf-pay-hand"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-bf-pay-hand" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-bf-pay-hand">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-POSTapi-v1-bf-pay-hand" data-method="POST"
      data-path="api/v1/bf/pay/hand"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-bf-pay-hand', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-bf-pay-hand"
                    onclick="tryItOut('POSTapi-v1-bf-pay-hand');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-bf-pay-hand"
                    onclick="cancelTryOut('POSTapi-v1-bf-pay-hand');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-bf-pay-hand"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/bf/pay/hand</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-v1-bf-pay-hand"
               value="Bearer {ACCESS_TOKEN}"
               data-component="header">
    <br>
<p><br>예시: <code>Bearer {ACCESS_TOKEN}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>pmod_id</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="pmod_id"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="1023"
               data-component="body">
    <br>
<p>결제모듈 고유번호. <br>예시: <code>1023</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>yymm</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="yymm"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="2311"
               data-component="body">
    <br>
<p>4자리 YYMM 유효기간. <br>예시: <code>2311</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>card_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="card_num"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="1234000000005678"
               data-component="body">
    <br>
<p>카드번호. <br>예시: <code>1234000000005678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>buyer_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="buyer_name"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="홍길동"
               data-component="body">
    <br>
<p>구매자명. <br>예시: <code>홍길동</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>buyer_phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="buyer_phone"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="01000000000"
               data-component="body">
    <br>
<p>휴대폰 번호. <br>예시: <code>01000000000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>installment</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="installment"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="0"
               data-component="body">
    <br>
<p>할부기간(0=일시불,2,3,4,5,6,7,8,9,10,11).<br>결제모듈의 할부한도를 초과할 수 없습니다. <br>예시: <code>0</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="amount"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="10000"
               data-component="body">
    <br>
<p>구매금액. <br>예시: <code>10000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ord_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ord_num"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="1700385517624H102302"
               data-component="body">
    <br>
<p>중복되지 않는 주문번호.<br>50자 이하로 작성해야합니다. <br>예시: <code>1700385517624H102302</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>item_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="item_name"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="메가커피 아메리카노 L"
               data-component="body">
    <br>
<p>상품명. <br>예시: <code>메가커피 아메리카노 L</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>auth_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i><b>optional</b></i> &nbsp;
                <input type="text" style="display: none"
                              name="auth_num"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="901212"
               data-component="body">
    <br>
<p>인증정보<b>(구인증 필수 값)</b>.<br>카도번호 소유주가 법인인경우 사업자번호, 개인인경우 주민등록번호 앞자리를 입력합니다. <br>예시: <code>901212</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>card_pw</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i><b>optional</b></i> &nbsp;
                <input type="text" style="display: none"
                              name="card_pw"                data-endpoint="POSTapi-v1-bf-pay-hand"
               value="34"
               data-component="body">
    <br>
<p>카드비밀번호 앞 2자리<b>(구인증 필수 값)</b>. <br>예시: <code>34</code></p>
        </div>
        </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 MID</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>tid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>단말기 TID</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>거래금액</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ord_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 주문번호</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>appr_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인번호</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>item_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>상품명</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>trx_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래번호</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>acquirer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>매입사명</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issuer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>발급사명</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>card_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>카드번호</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>installment</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>할부기간</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>method</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>결제방식</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>trx_dttm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래시간(Y-m-d H:i:s)</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_cancel</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소여부</p>
        </div>
                

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                                        <button type="button" class="lang-button" data-language-name="php">php</button>
                                                        <button type="button" class="lang-button" data-language-name="python">python</button>
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                            </div>
            </div>
</div>
</body>
</html>
