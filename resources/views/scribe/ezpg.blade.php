<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Ezpg API</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/utils/docs/theme-ezpg.style.css" media="screen">
    <link rel="stylesheet" href="/utils/docs/theme-ezpg.style.css" media="print">
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <link rel="dns-prefetch" href="https://w.ez-pg.kr">
    <link rel="icon" href="/storage/images/logos/KU9q8LxifqSQw0Mhbqi0cqFu2SLsvmTriTBt7BvI.svg"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="BuddyPay Mobile API">
    <meta property="og:image" content="https://team.payvery.kr/storage/images/ogs/170116003008dab237631d951bf4719ca827640d91.webp" >
    <meta property="og:description" content="본 문서는 EZPG와 EZPG Mobile간 인터페이스에 대하여 기술합니다.">
    <meta property="description" content="본 문서는 EZPG와 EZPG Mobile간 인터페이스에 대하여 기술합니다.">
    <meta name="author" content="purplevery">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BuddyPay">
    <meta name="theme-color" content="#003067">

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .javascript-example code { display: none; }
                    body .content .php-example code { display: none; }
                    body .content .python-example code { display: none; }
                    body .content .bash-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "https://w.ez-pg.kr";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="/vendor/scribe/js/tryitout-4.25.0.js"></script>
    <script src="/vendor/scribe/js/theme-default-4.25.0.js"></script>
</head>

<body data-languages="[&quot;javascript&quot;,&quot;php&quot;,&quot;python&quot;,&quot;bash&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="/vendor/scribe/images/navbar.png" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
        <div style="display: inline-flex; align-items: center; text-align: center; vertical-align: middle; padding: 1em; justify-content: left;">
        <img src="/storage/images/logos/KU9q8LxifqSQw0Mhbqi0cqFu2SLsvmTriTBt7BvI.svg" alt="logo" class="logo" width="20%"/>
        <span style='margin-left: 0.5em; font-size: 1.7em;'>이지피쥐</span>
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
                    <ul id="tocify-header-ezpg-api" class="tocify-header">
                <li class="tocify-item level-1" data-unique="ezpg-api">
                    <a href="#ezpg-api">EZPG API</a>
                </li>
                                    <ul id="tocify-subheader-ezpg-api" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="ezpg-api-POSTapi-v1-ezpg-sign-in">
                                <a href="#ezpg-api-POSTapi-v1-ezpg-sign-in">로그인</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="ezpg-api-GETapi-v1-ezpg-transactions">
                                <a href="#ezpg-api-GETapi-v1-ezpg-transactions">결제내역 조회</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="ezpg-api-GETapi-v1-ezpg-reconciliation-summary">
                                <a href="#ezpg-api-GETapi-v1-ezpg-reconciliation-summary">일별 요약 거래대사</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="ezpg-api-GETapi-v1-ezpg-reconciliation">
                                <a href="#ezpg-api-GETapi-v1-ezpg-reconciliation">실시간 거래대사</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                                        <li><a>Documentation powered by Payvery ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>마지막 업데이트:  2024-09-25 16:44:37</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="">소개</h1>
<p>본 문서는 EZPG와 Smart Data간 인터페이스에 대하여 기술합니다.</p>
<aside>
    <strong>기본 URL</strong>: <code>https://w.ez-pg.kr</code>
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

        <h1 id="ezpg-api">EZPG API</h1>

    <p>EZPG와 Smart Data간 API 입니다.</p>

                                <h2 id="ezpg-api-POSTapi-v1-ezpg-sign-in">로그인</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-ezpg-sign-in">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://w.ez-pg.kr/api/v1/ezpg/sign-in"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "user_name": "odio",
    "user_pw": "ea"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;post(
    'https://w.ez-pg.kr/api/v1/ezpg/sign-in',
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'user_name' =&gt; 'odio',
            'user_pw' =&gt; 'ea',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://w.ez-pg.kr/api/v1/ezpg/sign-in'
payload = {
    "user_name": "odio",
    "user_pw": "ea"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://w.ez-pg.kr/api/v1/ezpg/sign-in" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"user_name\": \"odio\",
    \"user_pw\": \"ea\"
}"
</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-ezpg-sign-in">
            <blockquote>
            <p>예시 응답 (200):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;access_token&quot;: &quot;9658|zoJ9RPe65DDaIor9jPapdpctuALtWkvMjGFrWn7a034f9c9f&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 12,
        &quot;user_name&quot;: &quot;test0001&quot;,
        &quot;level&quot;: 10,
        &quot;mcht_option&quot;: {
            &quot;downward_s_tm&quot;: &quot;23:00&quot;,
            &quot;downward_e_tm&quot;: &quot;07:00&quot;,
            &quot;downward_limit&quot;: 300
        },
        &quot;payment_option&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;mcht_id&quot;: 12,
                &quot;pay_year_limit&quot;: 10000,
                &quot;pay_month_limit&quot;: 1000,
                &quot;pay_day_limit&quot;: 30,
                &quot;pay_single_limit&quot;: 3,
                &quot;pay_disable_s_tm&quot;: &quot;23:00&quot;,
                &quot;pay_disable_e_tm&quot;: &quot;07:00&quot;
            },
            {
                &quot;id&quot;: 2,
                &quot;mcht_id&quot;: 12,
                &quot;pay_year_limit&quot;: 10000,
                &quot;pay_month_limit&quot;: 1000,
                &quot;pay_day_limit&quot;: 30,
                &quot;pay_single_limit&quot;: 3,
                &quot;pay_disable_s_tm&quot;: &quot;13:00&quot;,
                &quot;pay_disable_e_tm&quot;: &quot;23:00&quot;
            }
        ],
        &quot;specified_time_option&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;mcht_id&quot;: 12,
                &quot;disable_s_tm&quot;: &quot;23:00&quot;,
                &quot;disable_e_tm&quot;: &quot;07:00&quot;,
                &quot;disable_type&quot;: 0
            },
            {
                &quot;id&quot;: 2,
                &quot;mcht_id&quot;: 12,
                &quot;disable_s_tm&quot;: &quot;23:00&quot;,
                &quot;disable_e_tm&quot;: &quot;07:00&quot;,
                &quot;disable_type&quot;: 1
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-ezpg-sign-in" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-POSTapi-v1-ezpg-sign-in"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-ezpg-sign-in"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-ezpg-sign-in" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-ezpg-sign-in">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-POSTapi-v1-ezpg-sign-in" data-method="POST"
      data-path="api/v1/ezpg/sign-in"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-ezpg-sign-in', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #0d47a1; background-color: #0d47a1; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-ezpg-sign-in"
                    onclick="tryItOut('POSTapi-v1-ezpg-sign-in');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-ezpg-sign-in"
                    onclick="cancelTryOut('POSTapi-v1-ezpg-sign-in');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-ezpg-sign-in"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/ezpg/sign-in</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-ezpg-sign-in"
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
                              name="Accept"                data-endpoint="POSTapi-v1-ezpg-sign-in"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_name"                data-endpoint="POSTapi-v1-ezpg-sign-in"
               value="odio"
               data-component="body">
    <br>
<p>가맹점 아이디 <br>예시: <code>odio</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_pw</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="user_pw"                data-endpoint="POSTapi-v1-ezpg-sign-in"
               value="ea"
               data-component="body">
    <br>
<p>가맹점 패스워드 <br>예시: <code>ea</code></p>
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
                        <h2 id="ezpg-api-GETapi-v1-ezpg-transactions">결제내역 조회</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>로그인한 가맹점의 결제내역을 조회합니다.</p>

<span id="example-requests-GETapi-v1-ezpg-transactions">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://w.ez-pg.kr/api/v1/ezpg/transactions"
);

const params = {
    "page": "1",
    "page_size": "20",
    "s_dt": "2024-09-01 00:00:00",
    "e_dt": "2024-09-07 23:59:59",
    "search": "cupiditate",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

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
    'https://w.ez-pg.kr/api/v1/ezpg/transactions',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'page' =&gt; '1',
            'page_size' =&gt; '20',
            's_dt' =&gt; '2024-09-01 00:00:00',
            'e_dt' =&gt; '2024-09-07 23:59:59',
            'search' =&gt; 'cupiditate',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://w.ez-pg.kr/api/v1/ezpg/transactions'
params = {
  'page': '1',
  'page_size': '20',
  's_dt': '2024-09-01 00:00:00',
  'e_dt': '2024-09-07 23:59:59',
  'search': 'cupiditate',
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://w.ez-pg.kr/api/v1/ezpg/transactions?page=1&amp;page_size=20&amp;s_dt=2024-09-01+00%3A00%3A00&amp;e_dt=2024-09-07+23%3A59%3A59&amp;search=cupiditate" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-ezpg-transactions">
            <blockquote>
            <p>예시 응답 (201):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;page&quot;: &quot;1&quot;,
    &quot;page_size&quot;: &quot;20&quot;,
    &quot;total&quot;: 3,
    &quot;content&quot;: [
        {
            &quot;id&quot;: 545443,
            &quot;sales5_id&quot;: 9572,
            &quot;sales4_id&quot;: 9608,
            &quot;sales3_id&quot;: 9609,
            &quot;sales2_id&quot;: null,
            &quot;sales1_id&quot;: null,
            &quot;sales5_fee&quot;: 0.044,
            &quot;sales4_fee&quot;: 0.05,
            &quot;sales3_fee&quot;: 0.05,
            &quot;sales2_fee&quot;: 0,
            &quot;sales1_fee&quot;: 0,
            &quot;ps_fee&quot;: 0.015,
            &quot;mcht_fee&quot;: 0.08,
            &quot;hold_fee&quot;: 0,
            &quot;mcht_settle_fee&quot;: 0,
            &quot;is_cancel&quot;: 0,
            &quot;amount&quot;: 1000,
            &quot;module_type&quot;: 1,
            &quot;ord_num&quot;: &quot;57839H1700562509&quot;,
            &quot;mid&quot;: &quot;wel000764m&quot;,
            &quot;tid&quot;: &quot;57839&quot;,
            &quot;trx_id&quot;: &quot;202311210528348&quot;,
            &quot;ori_trx_id&quot;: null,
            &quot;card_num&quot;: &quot;467309******5166&quot;,
            &quot;issuer&quot;: &quot;국민&quot;,
            &quot;acquirer&quot;: &quot;국민&quot;,
            &quot;appr_num&quot;: &quot;30018849&quot;,
            &quot;installment&quot;: 0,
            &quot;buyer_name&quot;: &quot;ㅁㅁㅁ&quot;,
            &quot;buyer_phone&quot;: &quot;01000000000&quot;,
            &quot;item_name&quot;: &quot;ㅁㅁㅁㅁㅁ&quot;,
            &quot;note&quot;: &quot;수기결제&quot;,
            &quot;cxl_type&quot;: 1,
            &quot;trx_dttm&quot;: &quot; &quot;,
            &quot;cxl_dttm&quot;: &quot; &quot;,
            &quot;profit&quot;: 920,
            &quot;sales0_name&quot;: &quot;&quot;,
            &quot;sales1_name&quot;: &quot;&quot;,
            &quot;sales2_name&quot;: &quot;&quot;,
            &quot;sales3_name&quot;: &quot;지사1&quot;,
            &quot;sales4_name&quot;: &quot;총판1&quot;,
            &quot;sales5_name&quot;: &quot;대리점1&quot;,
            &quot;trx_amount&quot;: 1000,
            &quot;hold_amount&quot;: 0,
            &quot;total_trx_amount&quot;: 1000
        },
        {
            &quot;id&quot;: 543690,
            &quot;sales5_id&quot;: 9572,
            &quot;sales4_id&quot;: 9608,
            &quot;sales3_id&quot;: 9609,
            &quot;sales2_id&quot;: null,
            &quot;sales1_id&quot;: null,
            &quot;sales5_fee&quot;: 0.044,
            &quot;sales4_fee&quot;: 0.05,
            &quot;sales3_fee&quot;: 0.05,
            &quot;sales2_fee&quot;: 0,
            &quot;sales1_fee&quot;: 0,
            &quot;ps_fee&quot;: 0.015,
            &quot;mcht_fee&quot;: 0.08,
            &quot;hold_fee&quot;: 0,
            &quot;mcht_settle_fee&quot;: 0,
            &quot;is_cancel&quot;: 1,
            &quot;amount&quot;: -1000,
            &quot;module_type&quot;: 1,
            &quot;ord_num&quot;: &quot;57839H1700548889&quot;,
            &quot;mid&quot;: &quot;wel000764m&quot;,
            &quot;tid&quot;: &quot;57839&quot;,
            &quot;trx_id&quot;: &quot;202311210523140&quot;,
            &quot;ori_trx_id&quot;: &quot;202311210523140&quot;,
            &quot;card_num&quot;: &quot;538720******2287&quot;,
            &quot;issuer&quot;: &quot;우리&quot;,
            &quot;acquirer&quot;: &quot;우리&quot;,
            &quot;appr_num&quot;: &quot;33597580&quot;,
            &quot;installment&quot;: 0,
            &quot;buyer_name&quot;: &quot;ㅁㅁㅁ&quot;,
            &quot;buyer_phone&quot;: &quot;01000000000&quot;,
            &quot;item_name&quot;: &quot;ㅁㅁㅁㅁㅁ&quot;,
            &quot;note&quot;: &quot;수기결제&quot;,
            &quot;cxl_type&quot;: 1,
            &quot;trx_dttm&quot;: &quot; &quot;,
            &quot;cxl_dttm&quot;: &quot; &quot;,
            &quot;profit&quot;: -920,
            &quot;sales0_name&quot;: &quot;&quot;,
            &quot;sales1_name&quot;: &quot;&quot;,
            &quot;sales2_name&quot;: &quot;&quot;,
            &quot;sales3_name&quot;: &quot;지사1&quot;,
            &quot;sales4_name&quot;: &quot;총판1&quot;,
            &quot;sales5_name&quot;: &quot;대리점1&quot;,
            &quot;trx_amount&quot;: -1000,
            &quot;hold_amount&quot;: 0,
            &quot;total_trx_amount&quot;: -1000
        },
        {
            &quot;id&quot;: 543678,
            &quot;sales5_id&quot;: 9572,
            &quot;sales4_id&quot;: 9608,
            &quot;sales3_id&quot;: 9609,
            &quot;sales2_id&quot;: null,
            &quot;sales1_id&quot;: null,
            &quot;sales5_fee&quot;: 0.044,
            &quot;sales4_fee&quot;: 0.05,
            &quot;sales3_fee&quot;: 0.05,
            &quot;sales2_fee&quot;: 0,
            &quot;sales1_fee&quot;: 0,
            &quot;ps_fee&quot;: 0.015,
            &quot;mcht_fee&quot;: 0.08,
            &quot;hold_fee&quot;: 0,
            &quot;mcht_settle_fee&quot;: 0,
            &quot;is_cancel&quot;: 0,
            &quot;amount&quot;: 1000,
            &quot;module_type&quot;: 1,
            &quot;ord_num&quot;: &quot;57839H1700548889&quot;,
            &quot;mid&quot;: &quot;wel000764m&quot;,
            &quot;tid&quot;: &quot;57839&quot;,
            &quot;trx_id&quot;: &quot;202311210523140&quot;,
            &quot;ori_trx_id&quot;: null,
            &quot;card_num&quot;: &quot;538720******2287&quot;,
            &quot;issuer&quot;: &quot;우리&quot;,
            &quot;acquirer&quot;: &quot;우리&quot;,
            &quot;appr_num&quot;: &quot;33597580&quot;,
            &quot;installment&quot;: 0,
            &quot;buyer_name&quot;: &quot;ㅁㅁㅁ&quot;,
            &quot;buyer_phone&quot;: &quot;01000000000&quot;,
            &quot;item_name&quot;: &quot;ㅁㅁㅁㅁㅁ&quot;,
            &quot;note&quot;: &quot;수기결제&quot;,
            &quot;cxl_type&quot;: 1,
            &quot;trx_dttm&quot;: &quot; &quot;,
            &quot;cxl_dttm&quot;: &quot; &quot;,
            &quot;profit&quot;: 920,
            &quot;sales0_name&quot;: &quot;&quot;,
            &quot;sales1_name&quot;: &quot;&quot;,
            &quot;sales2_name&quot;: &quot;&quot;,
            &quot;sales3_name&quot;: &quot;지사1&quot;,
            &quot;sales4_name&quot;: &quot;총판1&quot;,
            &quot;sales5_name&quot;: &quot;대리점1&quot;,
            &quot;trx_amount&quot;: 1000,
            &quot;hold_amount&quot;: 0,
            &quot;total_trx_amount&quot;: 1000
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-ezpg-transactions" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-GETapi-v1-ezpg-transactions"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-ezpg-transactions"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-ezpg-transactions" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-ezpg-transactions">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-GETapi-v1-ezpg-transactions" data-method="GET"
      data-path="api/v1/ezpg/transactions"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-ezpg-transactions', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #0d47a1; background-color: #0d47a1; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-ezpg-transactions"
                    onclick="tryItOut('GETapi-v1-ezpg-transactions');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-ezpg-transactions"
                    onclick="cancelTryOut('GETapi-v1-ezpg-transactions');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-ezpg-transactions"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/ezpg/transactions</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-ezpg-transactions"
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
                              name="Content-Type"                data-endpoint="GETapi-v1-ezpg-transactions"
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
                              name="Accept"                data-endpoint="GETapi-v1-ezpg-transactions"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-v1-ezpg-transactions"
               value="1"
               data-component="query">
    <br>
<p>조회 페이지 <br>예시: <code>1</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page_size</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page_size"                data-endpoint="GETapi-v1-ezpg-transactions"
               value="20"
               data-component="query">
    <br>
<p>조회 사이즈 <br>예시: <code>20</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>s_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="s_dt"                data-endpoint="GETapi-v1-ezpg-transactions"
               value="2024-09-01 00:00:00"
               data-component="query">
    <br>
<p>검색 시작일 <br>예시: <code>2024-09-01 00:00:00</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>e_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="e_dt"                data-endpoint="GETapi-v1-ezpg-transactions"
               value="2024-09-07 23:59:59"
               data-component="query">
    <br>
<p>검색 종료일 <br>예시: <code>2024-09-07 23:59:59</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i><b>optional</b></i> &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="GETapi-v1-ezpg-transactions"
               value="cupiditate"
               data-component="query">
    <br>
<p>검색어(MID, TID, 거래번호, 승인번호, 발급사, 매입사, 결제모듈 별칭) <br>예시: <code>cupiditate</code></p>
            </div>
                </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>조회 페이지</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>page_size</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>조회 사이즈</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>total</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>총 개수</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>content</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
<br>
<p>결과</p>
            </summary>
                                                <div style=" margin-left: 14px; clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>*</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>ps_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>PG사 구간 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>sales4_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>영업점 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>sales3_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>지사 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>sales2_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>총판 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>sales1_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>대리점 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>mcht_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>hold_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>유보금 수수료(%)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>is_cancel</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소여부(0=승인, 1=취소)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_type</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소 타입취소타입(0=취소금지, 1=이체시간 -5분, 2=당일허용)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>거래금액</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>profit</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 정산금액</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 거래 수수료</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>mcht_settle_fee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 건별 수수료</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>total_trx_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 총 거래 수수료(건별 수수료 + 거래 수수료)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>hold_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 유보금 수수료</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인일자</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_tm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인시간</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>취소일자(취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_tm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>취소시간(취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_seq</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소회차(취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>ori_trx_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>원거래에 사용된 transaction ID (취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>ord_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 order number</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 transaction ID</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>mid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 PG사 MID</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>tid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 terminal ID</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>card_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>마스킹된 카드번호</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>issuer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>발급사</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>acquirer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>매입사</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>installment</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>할부개월(0~12)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>appr_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인번호</p>
                    </div>
                                    </details>
        </div>
                                        </details>
        </div>
                        <h2 id="ezpg-api-GETapi-v1-ezpg-reconciliation-summary">일별 요약 거래대사</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>특정 일의 결제내역을 조회합니다.</p>

<span id="example-requests-GETapi-v1-ezpg-reconciliation-summary">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://w.ez-pg.kr/api/v1/ezpg/reconciliation/summary"
);

const params = {
    "t_dt": "2024-09-01",
    "search": "rerum",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

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
    'https://w.ez-pg.kr/api/v1/ezpg/reconciliation/summary',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            't_dt' =&gt; '2024-09-01',
            'search' =&gt; 'rerum',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://w.ez-pg.kr/api/v1/ezpg/reconciliation/summary'
params = {
  't_dt': '2024-09-01',
  'search': 'rerum',
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://w.ez-pg.kr/api/v1/ezpg/reconciliation/summary?t_dt=2024-09-01&amp;search=rerum" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-ezpg-reconciliation-summary">
            <blockquote>
            <p>예시 응답 (201):</p>
        </blockquote>
                <pre>

<code class="language-json">[
    {
        &quot;id&quot;: 12345,
        &quot;user_name&quot;: &quot;test1235&quot;,
        &quot;mcht_name&quot;: &quot;테스트 가맹점&quot;,
        &quot;appr_amount&quot;: 1000,
        &quot;appr_count&quot;: 1,
        &quot;cxl_amount&quot;: -1000,
        &quot;cxl_count&quot;: 1,
        &quot;sales_amount&quot;: 0,
        &quot;total_count&quot;: 2
    },
    {
        &quot;id&quot;: 181244,
        &quot;user_name&quot;: &quot;test1234&quot;,
        &quot;mcht_name&quot;: &quot;테스트 가맹점2&quot;,
        &quot;appr_amount&quot;: 934000,
        &quot;appr_count&quot;: 11,
        &quot;cxl_amount&quot;: 0,
        &quot;cxl_count&quot;: 0,
        &quot;sales_amount&quot;: 934000,
        &quot;total_count&quot;: 11
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-ezpg-reconciliation-summary" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-GETapi-v1-ezpg-reconciliation-summary"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-ezpg-reconciliation-summary"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-ezpg-reconciliation-summary" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-ezpg-reconciliation-summary">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-GETapi-v1-ezpg-reconciliation-summary" data-method="GET"
      data-path="api/v1/ezpg/reconciliation/summary"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-ezpg-reconciliation-summary', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #0d47a1; background-color: #0d47a1; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-ezpg-reconciliation-summary"
                    onclick="tryItOut('GETapi-v1-ezpg-reconciliation-summary');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-ezpg-reconciliation-summary"
                    onclick="cancelTryOut('GETapi-v1-ezpg-reconciliation-summary');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-ezpg-reconciliation-summary"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/ezpg/reconciliation/summary</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-ezpg-reconciliation-summary"
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
                              name="Content-Type"                data-endpoint="GETapi-v1-ezpg-reconciliation-summary"
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
                              name="Accept"                data-endpoint="GETapi-v1-ezpg-reconciliation-summary"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>t_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="t_dt"                data-endpoint="GETapi-v1-ezpg-reconciliation-summary"
               value="2024-09-01"
               data-component="query">
    <br>
<p>조회일 <br>예시: <code>2024-09-01</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i><b>optional</b></i> &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="GETapi-v1-ezpg-reconciliation-summary"
               value="rerum"
               data-component="query">
    <br>
<p>검색어(MID, TID, 거래번호, 승인번호, 발급사, 매입사, 결제모듈 별칭) <br>예시: <code>rerum</code></p>
            </div>
                </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 ID</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mcht_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>가맹점 상호</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>appr_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>승인 금액 합계</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>appr_count</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>승인 건수 합계</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cxl_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소 금액 합계</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cxl_count</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소 건수 합계</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sales_amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>매출(승인+취소) 합계</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>total_count</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>총 건수</p>
        </div>
                        <h2 id="ezpg-api-GETapi-v1-ezpg-reconciliation">실시간 거래대사</h2>

<p>
<small class="badge badge-darkred">인증 필요</small>
</p>

<p>특정 기간의 결제내역을 조회합니다.</p>

<span id="example-requests-GETapi-v1-ezpg-reconciliation">
<blockquote>예시 요청:</blockquote>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://w.ez-pg.kr/api/v1/ezpg/reconciliation"
);

const params = {
    "page": "1",
    "page_size": "20",
    "s_dt": "2024-09-01 00:00:00",
    "e_dt": "2024-09-07 23:59:59",
    "search": "magnam",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

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
    'https://w.ez-pg.kr/api/v1/ezpg/reconciliation',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'page' =&gt; '1',
            'page_size' =&gt; '20',
            's_dt' =&gt; '2024-09-01 00:00:00',
            'e_dt' =&gt; '2024-09-07 23:59:59',
            'search' =&gt; 'magnam',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'https://w.ez-pg.kr/api/v1/ezpg/reconciliation'
params = {
  'page': '1',
  'page_size': '20',
  's_dt': '2024-09-01 00:00:00',
  'e_dt': '2024-09-07 23:59:59',
  'search': 'magnam',
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre></div>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://w.ez-pg.kr/api/v1/ezpg/reconciliation?page=1&amp;page_size=20&amp;s_dt=2024-09-01+00%3A00%3A00&amp;e_dt=2024-09-07+23%3A59%3A59&amp;search=magnam" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-ezpg-reconciliation">
            <blockquote>
            <p>예시 응답 (201):</p>
        </blockquote>
                <pre>

<code class="language-json">{
    &quot;page&quot;: &quot;1&quot;,
    &quot;page_size&quot;: &quot;20&quot;,
    &quot;total&quot;: 2,
    &quot;content&quot;: [
        {
            &quot;is_cancel&quot;: 0,
            &quot;trx_dt&quot;: &quot;2023-11-01&quot;,
            &quot;trx_tm&quot;: &quot;14:20:15&quot;,
            &quot;cxl_dt&quot;: null,
            &quot;cxl_tm&quot;: null,
            &quot;cxl_seq&quot;: 0,
            &quot;ori_trx_id&quot;: null,
            &quot;amount&quot;: 1000,
            &quot;ord_num&quot;: &quot;57839H1700562509&quot;,
            &quot;trx_id&quot;: &quot;202311210528348&quot;,
            &quot;mid&quot;: &quot;PVPG_C3568479&quot;,
            &quot;tid&quot;: &quot;2824020001&quot;,
            &quot;card_num&quot;: &quot;467309******5166&quot;,
            &quot;issuer&quot;: &quot;국민&quot;,
            &quot;acquirer&quot;: &quot;국민&quot;,
            &quot;installment&quot;: 0,
            &quot;appr_num&quot;: &quot;30018849&quot;
        },
        {
            &quot;is_cancel&quot;: 1,
            &quot;trx_dt&quot;: &quot;2023-11-01&quot;,
            &quot;trx_tm&quot;: &quot;14:25:15&quot;,
            &quot;cxl_dt&quot;: &quot;2023-11-01&quot;,
            &quot;cxl_tm&quot;: &quot;17:30:37&quot;,
            &quot;cxl_seq&quot;: 1,
            &quot;ori_trx_id&quot;: &quot;202311210528348&quot;,
            &quot;amount&quot;: -1000,
            &quot;ord_num&quot;: &quot;57839H1700562509&quot;,
            &quot;trx_id&quot;: &quot;202311210528413&quot;,
            &quot;mid&quot;: &quot;PVPG_C3568479&quot;,
            &quot;tid&quot;: &quot;2824020001&quot;,
            &quot;card_num&quot;: &quot;467309******5166&quot;,
            &quot;issuer&quot;: &quot;국민&quot;,
            &quot;acquirer&quot;: &quot;국민&quot;,
            &quot;installment&quot;: 0,
            &quot;appr_num&quot;: &quot;30018849&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-ezpg-reconciliation" hidden>
    <blockquote>받은 응답<span
                id="execution-response-status-GETapi-v1-ezpg-reconciliation"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-ezpg-reconciliation"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-ezpg-reconciliation" hidden>
    <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-ezpg-reconciliation">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
</span>
<form id="form-GETapi-v1-ezpg-reconciliation" data-method="GET"
      data-path="api/v1/ezpg/reconciliation"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-ezpg-reconciliation', this);">
    <h3>
        요청&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="border-color: #0d47a1; background-color: #0d47a1; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-ezpg-reconciliation"
                    onclick="tryItOut('GETapi-v1-ezpg-reconciliation');">시도하기 ⚡
            </button>
            <button type="button"
                    style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-ezpg-reconciliation"
                    onclick="cancelTryOut('GETapi-v1-ezpg-reconciliation');" hidden>취소 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-ezpg-reconciliation"
                    data-initial-text="요청 💥"
                    data-loading-text="⏱ 요청중..."
                    hidden>요청 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/ezpg/reconciliation</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>헤더</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-v1-ezpg-reconciliation"
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
                              name="Content-Type"                data-endpoint="GETapi-v1-ezpg-reconciliation"
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
                              name="Accept"                data-endpoint="GETapi-v1-ezpg-reconciliation"
               value="application/json"
               data-component="header">
    <br>
<p><br>예시: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-v1-ezpg-reconciliation"
               value="1"
               data-component="query">
    <br>
<p>조회 페이지 <br>예시: <code>1</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page_size</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page_size"                data-endpoint="GETapi-v1-ezpg-reconciliation"
               value="20"
               data-component="query">
    <br>
<p>조회 사이즈 <br>예시: <code>20</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>s_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="s_dt"                data-endpoint="GETapi-v1-ezpg-reconciliation"
               value="2024-09-01 00:00:00"
               data-component="query">
    <br>
<p>검색 시작일 <br>예시: <code>2024-09-01 00:00:00</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>e_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="e_dt"                data-endpoint="GETapi-v1-ezpg-reconciliation"
               value="2024-09-07 23:59:59"
               data-component="query">
    <br>
<p>검색 종료일 <br>예시: <code>2024-09-07 23:59:59</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i><b>optional</b></i> &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="GETapi-v1-ezpg-reconciliation"
               value="magnam"
               data-component="query">
    <br>
<p>검색어(MID, TID, 거래번호, 승인번호, 발급사, 매입사, 결제모듈 별칭) <br>예시: <code>magnam</code></p>
            </div>
                </form>

    <h3>응답</h3>
    <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>조회 페이지</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>page_size</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>조회 사이즈</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>total</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>총 개수</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>content</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
<br>
<p>결과</p>
            </summary>
                                                <div style=" margin-left: 14px; clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>*</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>is_cancel</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소여부(0=승인, 1=취소)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인일자</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_tm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인시간</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_dt</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>취소일자(취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_tm</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>취소시간(취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>cxl_seq</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>취소회차(취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>ori_trx_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>원거래에 사용된 transaction ID (취소일 경우)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>거래금액</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>ord_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 order number</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>trx_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 transaction ID</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>mid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 PG사 MID</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>tid</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>거래에 사용된 terminal ID</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>card_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>마스킹된 카드번호</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>issuer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>발급사</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>acquirer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>매입사</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>installment</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
<br>
<p>할부개월(0~12)</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>appr_num</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
<br>
<p>승인번호</p>
                    </div>
                                    </details>
        </div>
                                        </details>
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
