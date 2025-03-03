<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>SAMW API</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/utils/docs/theme-comagain.style.css" media="screen">
    <link rel="stylesheet" href="/utils/docs/theme-comagain.style.css" media="print">
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>
    <style id="language-style">
      /* starts out as display none and is replaced with js later  */
      body .content .javascript-example code {
        display: none;
      }

      body .content .php-example code {
        display: none;
      }

      body .content .python-example code {
        display: none;
      }

      body .content .bash-example code {
        display: none;
      }
    </style>
    <script>
      var tryItOutBaseUrl = "https://www.routeup.kr";
      var useCsrf = Boolean();
      var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="/vendor/scribe/js/tryitout-4.38.0.js"></script>
    <script src="/vendor/scribe/js/theme-default-4.38.0.js"></script>
  </head>
  <body data-languages="[&quot;javascript&quot;,&quot;php&quot;,&quot;python&quot;,&quot;bash&quot;]"><a href="#" id="nav-button"><span> MENU <img src="/vendor/scribe/images/navbar.png" alt="navbar-image" /></span></a>
    <div class="tocify-wrapper">
      <div style="display: inline-flex; align-items: center; text-align: center; vertical-align: middle; padding: 1em; justify-content: left;"><img src="/utils/logo.svg" alt="logo" class="logo" width="20%" /><span style='margin-left: 0.5em; font-size: 1.7em;'>PAYVERY</span></div>
      <div class="lang-selector"><button type="button" class="lang-button" data-language-name="javascript">javascript</button><button type="button" class="lang-button" data-language-name="php">php</button><button type="button" class="lang-button" data-language-name="python">python</button><button type="button" class="lang-button" data-language-name="bash">bash</button></div>
      <div class="search"><input type="text" class="search" id="input-search" placeholder="검색"></div>
      <div id="toc">
        <ul id="tocify-header-intro" class="tocify-header">
          <li class="tocify-item level-1" data-unique="intro"><a href="#intro">소개</a></li>
        </ul>
        <ul id="tocify-header-samw-auth" class="tocify-header">
          <li class="tocify-item level-1" data-unique="samw-auth"><a href="#samw-auth">인증 필요</a></li>
        </ul>
        <ul id="tocify-header-samw-bank" class="tocify-header">
          <li class="tocify-item level-1" data-unique="samw-bank"><a href="#samw-bank">금융기관 정의</a></li>
        </ul>
        <ul id="tocify-header-samw-api" class="tocify-header">
            <li class="tocify-item level-1" data-unique="noti-format">
                <a href="#noti-format" data-jets="통지 API">통지 API</a>
            </li>
            <ul id="tocify-header-noti" class="tocify-subheader">
                <li class="tocify-item level-2" data-unique="noti-request-format">
                    <a href="#noti-request-format" data-jets="통지 전송 규격">통지 전송 규격</a>
                </li>
                <li class="tocify-item level-2" data-unique="noti-response-format">
                    <a href="#noti-response-format" data-jets="통지 응답 규격">통지 응답 규격</a>
                </li>
            </ul>
        </ul>
        <ul id="tocify-header-samw-api" class="tocify-header">
          <li class="tocify-item level-1" data-unique="samw-api"><a href="#samw-api">SAMW API</a></li>
          <ul id="tocify-subheader-samw-api" class="tocify-subheader">
            <li class="tocify-item level-2" data-unique="samw-api-POSTapi-v1-samw-sign-in"><a href="#samw-api-POSTapi-v1-samw-sign-in">로그인</a></li>
            <li class="tocify-item level-2" data-unique="samw-api-GETapi-v1-samw-withdraws-balance"><a href="#samw-api-GETapi-v1-samw-withdraws-balance">출금가능금액 조회</a></li>
            <li class="tocify-item level-2" data-unique="samw-api-POSTapi-v1-samw-withdraws"><a href="#samw-api-POSTapi-v1-samw-withdraws">출금요청</a></li>
          </ul>
        </ul>
      </div>
      <ul class="toc-footer" id="toc-footer">
        <li><a>Documentation powered by Payvery ✍</a></li>
      </ul>
      <ul class="toc-footer" id="last-updated">
        <li>마지막 업데이트: 2025-03-03 22:01:20</li>
      </ul>
    </div>
    <div class="page-wrapper">
      <div class="dark-box"></div>
      <div class="content">
        <h1 id="samw">소개</h1>
        <p>본 문서는 루트업의 SAMW API 인터페이스에 대하여 기술합니다.</p>
        <aside><strong>기본 URL</strong>: <code>https://www.routeup.kr</code></aside>
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
                <td>추가,수정,작업 성공</td>
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
        <h1 id="samw-auth">인증 필요</h1>
        <p>요청을 인증하려면 <strong><code>"Bearer {ACCESS_TOKEN}"</code></strong> 값과 함께 <strong><code>Authorization</code></strong> 헤더를 포함하세요.</p>
        <p>인증이 요구되는 모든 엔드포인트에는 아래 문서에 <small class="badge badge-darkred">인증 필요</small> 배지가 표시되어 있습니다.</p>
        <h1 id="samw-bank">금융기관 정의</h1>
        <p><b>"SAMW API - 출금 요청"</b>단락에서 은행정보가 사용됩니다.</p>
        <p><b>acct_bank_code, acct_bank_name</b> 파라미터에 하기 내용과 동일한 값을 넣어주셔야 정상적으로 작동합니다.</p>
        <p>
            <b style='color:red'>금융 VAN사 별로 동작하지 않는 금융기관이 존재할 수 있습니다.</b>
        </p>
          <table>
            <thead>
                <tr>
                <th>은행코드</th>
                <th>은행명</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td>001</td>
                <td>한국은행</td>
                </tr>
                <tr>
                <td>002</td>
                <td>산업은행</td>
                </tr>
                <tr>
                <td>003</td>
                <td>기업은행</td>
                </tr>
                <tr>
                <td>004</td>
                <td>국민은행</td>
                </tr>
                <tr>
                <td>005</td>
                <td>외환은행</td>
                </tr>
                <tr>
                <td>007</td>
                <td>수협은행</td>
                </tr>
                <tr>
                <td>008</td>
                <td>수출입은행</td>
                </tr>
                <tr>
                <td>011</td>
                <td>농협은행</td>
                </tr>
                <tr>
                <td>012</td>
                <td>농협회원조합</td>
                </tr>
                <tr>
                <td>020</td>
                <td>우리은행</td>
                </tr>
                <tr>
                <td>023</td>
                <td>SC제일은행</td>
                </tr>
                <tr>
                <td>026</td>
                <td>서울은행</td>
                </tr>
                <tr>
                <td>027</td>
                <td>한국씨티은행</td>
                </tr>
                <tr>
                <td>031</td>
                <td>대구은행</td>
                </tr>
                <tr>
                <td>032</td>
                <td>부산은행</td>
                </tr>
                <tr>
                <td>034</td>
                <td>광주은행</td>
                </tr>
                <tr>
                <td>035</td>
                <td>제주은행</td>
                </tr>
                <tr>
                <td>037</td>
                <td>전북은행</td>
                </tr>
                <tr>
                <td>039</td>
                <td>경남은행</td>
                </tr>
                <tr>
                <td>045</td>
                <td>새마을금고연합회</td>
                </tr>
                <tr>
                <td>048</td>
                <td>신협중앙회</td>
                </tr>
                <tr>
                <td>050</td>
                <td>상호저축은행</td>
                </tr>
                <tr>
                <td>051</td>
                <td>기타 외국계은행</td>
                </tr>
                <tr>
                <td>052</td>
                <td>모건스탠리은행</td>
                </tr>
                <tr>
                <td>054</td>
                <td>HSBC은행</td>
                </tr>
                <tr>
                <td>055</td>
                <td>도이치은행</td>
                </tr>
                <tr>
                <td>056</td>
                <td>알비에스피엘씨은행</td>
                </tr>
                <tr>
                <td>057</td>
                <td>제이피모간체이스은행</td>
                </tr>
                <tr>
                <td>058</td>
                <td>미즈호코퍼레이트은행</td>
                </tr>
                <tr>
                <td>059</td>
                <td>미쓰비시도쿄UFJ은행</td>
                </tr>
                <tr>
                <td>060</td>
                <td>BOA</td>
                </tr>
                <tr>
                <td>061</td>
                <td>비엔피파리바은행</td>
                </tr>
                <tr>
                <td>062</td>
                <td>중국공상은행</td>
                </tr>
                <tr>
                <td>063</td>
                <td>중국은행</td>
                </tr>
                <tr>
                <td>064</td>
                <td>산림조합</td>
                </tr>
                <tr>
                <td>065</td>
                <td>대화은행</td>
                </tr>
                <tr>
                <td>071</td>
                <td>우체국</td>
                </tr>
                <tr>
                <td>076</td>
                <td>신용보증기금</td>
                </tr>
                <tr>
                <td>077</td>
                <td>기술신용보증기금</td>
                </tr>
                <tr>
                <td>081</td>
                <td>하나은행</td>
                </tr>
                <tr>
                <td>088</td>
                <td>신한은행</td>
                </tr>
                <tr>
                <td>089</td>
                <td>케이뱅크</td>
                </tr>
                <tr>
                <td>090</td>
                <td>카카오뱅크</td>
                </tr>
                <tr>
                <td>092</td>
                <td>토스뱅크</td>
                </tr>
                <tr>
                <td>094</td>
                <td>서울보증보험</td>
                </tr>
                <tr>
                <td>101</td>
                <td>한국신용정보원</td>
                </tr>
                <tr>
                <td>103</td>
                <td>SBI저축은행</td>
                </tr>
                <tr>
                <td>105</td>
                <td>웰컴저축은행</td>
                </tr>
            </tbody>
        </table>
        <h1 id="noti-format">통지 API</h1>
        <p style="font-weight:bold;">방화벽 및 보안 설정 필요시 하단 아이피를 추가합니다.</p>
        <p style="font-weight:bold;">Webhook server IP : 221.168.33.227</p>

        <h2 id="noti-request-format">통지 전송 규격</h2>
        <p>루트업 전산내에서 출금시 전산내 저장되어있는 통지 URL로 하기 정보들이 전달됩니다.</p>
        <table>
            <thead>
                <tr>
                <th>요청헤더 명</th>
                <th>요청 값</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Content-Type</td>
                    <td>application/json</td>
                </tr>
                <tr>
                    <td>Accept</td>
                    <td>application/json</td>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th>필드명</th>
                    <th>필드 ID</th>
                    <th>비고</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>SAMW CODE</td>
                    <td>samw_code</td>
                    <td></td>
                </tr>
                <tr>
                    <td>거래금액</td>
                    <td>trans_amount</td>
                    <td>1000</td>
                </tr>
                <tr>
                    <td>거래타입</td>
                    <td>trans_type</td>
                    <td>1고정 (0=입금, 1=출금)</td>
                </tr>
                <tr>
                    <td>거래시간</td>
                    <td>trans_at</td>
                    <td>2025-03-03 10:49:33</td>
                </tr>
            </tbody>
        </table>          
        <h2 id="noti-response-format">통지 응답 규격</h2>
        <p>
          <b>통지를 성공적으로 받았을 시, body에 작성하셔야할 내용입니다.</b>
        </p>
        <aside>
            1. 하단 테이블의 포멧에 맞게 응답하셔야 통지서버에서 성공/실패유무를 판단하여 재전송하지 않습니다.<br><br>
            2. 응답 포멧이 다를시, 가맹점 별 설정해둔 재전송 회수만큼 1분 간격으로 재전송 합니다.<br><br>
            3. body encoding format은 UTF-8으로 응답해야 합니다.
        </aside>
        <table>
            <thead>
                <tr>
                    <th>상황별 응답</th>
                    <th>http code</th>
                    <th>body</th>
                    <th>비고</th>
                </tr>
                <tr>
                    <td>성공시 응답</td>
                    <td>200</td>
                    <td>{}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>실패시 응답</td>
                    <td>200 외 http status code</td>
                    <td>{"message":"실패한 사유"}</td>
                    <td>리턴하신 body 내용은 전산내 출금노티전송이력에 표시됩니다.</td>
                </tr>
            </thead>
        </table>
        <h1 id="samw-api">SAMW API</h1>
          <p><b>본 API를 이용하기 위해서 전산내 IP 등록이 필요합니다.</b></p>
          <h2 id="samw-api-POSTapi-v1-samw-sign-in">로그인</h2>
          <p></p><span id="example-requests-POSTapi-v1-samw-sign-in">
            <blockquote>예시 요청:</blockquote>
            <div class="javascript-example">
              <pre><code class="language-javascript">const url = new URL(
    "https://www.routeup.kr/api/v1/samw/sign-in"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "External-Api": "Bearer {API_KEY}",
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
}).then(response =&gt; response.json());</code></pre>
            </div>
            <div class="php-example">
              <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;post(
    'https://www.routeup.kr/api/v1/samw/sign-in',
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'External-Api' =&gt; 'Bearer {API_KEY}',
        ],
        'json' =&gt; [
            'brand_id' =&gt; 12,
            'user_name' =&gt; 'test0001',
            'user_pw' =&gt; 'test0001',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
            </div>
            <div class="python-example">
              <pre><code class="language-python">import requests
import json

url = 'https://www.routeup.kr/api/v1/samw/sign-in'
payload = {
    "brand_id": 12,
    "user_name": "test0001",
    "user_pw": "test0001"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'External-Api': 'Bearer {API_KEY}'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>
            </div>
            <div class="bash-example">
              <pre><code class="language-bash">curl --request POST \
    "https://www.routeup.kr/api/v1/samw/sign-in" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "External-Api: Bearer {API_KEY}" \
    --data "{
    \"brand_id\": 12,
    \"user_name\": \"test0001\",
    \"user_pw\": \"test0001\"
}"
</code></pre>
            </div>
          </span><span id="example-responses-POSTapi-v1-samw-sign-in">
            <blockquote>
              <p>예시 응답 (200):</p>
            </blockquote>
            <pre><code class="language-json">{
    &quot;access_token&quot;: &quot;9658|zoJ9RPe65DDaIor9jPapdpctuALtWkvMjGFrWn7a034f9c9f&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 12,
        &quot;user_name&quot;: &quot;test0001&quot;,
        &quot;level&quot;: 10
    }
}</code></pre>
          </span><span id="execution-results-POSTapi-v1-samw-sign-in" hidden>
            <blockquote>받은 응답<span id="execution-response-status-POSTapi-v1-samw-sign-in"></span>: </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v1-samw-sign-in"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
          </span><span id="execution-error-POSTapi-v1-samw-sign-in" hidden>
            <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v1-samw-sign-in">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
          </span>
          <form id="form-POSTapi-v1-samw-sign-in" data-method="POST" data-path="api/v1/samw/sign-in" data-authed="0" data-hasfiles="0" data-isarraybody="0" autocomplete="off" onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-samw-sign-in', this);">
            <h3> 요청&nbsp;&nbsp;&nbsp; <button type="button" style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v1-samw-sign-in" onclick="tryItOut('POSTapi-v1-samw-sign-in');">시도하기 ⚡ </button><button type="button" style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v1-samw-sign-in" onclick="cancelTryOut('POSTapi-v1-samw-sign-in');" hidden>취소 🛑 </button>&nbsp;&nbsp; <button type="submit" style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v1-samw-sign-in" data-initial-text="요청 💥" data-loading-text="⏱ 요청중..." hidden>요청 💥 </button></h3>
            <p><small class="badge badge-black">POST</small><b><code>api/v1/samw/sign-in</code></b></p>
            <h4 class="fancy-heading-panel"><b>헤더</b></h4>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Content-Type" data-endpoint="POSTapi-v1-samw-sign-in" value="application/json" data-component="header"><br>
              <p><br>예시: <code>application/json</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Accept" data-endpoint="POSTapi-v1-samw-sign-in" value="application/json" data-component="header"><br>
              <p><br>예시: <code>application/json</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>External-Api</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="External-Api" data-endpoint="POSTapi-v1-samw-sign-in" value="Bearer {API_KEY}" data-component="header"><br>
              <p><br>예시: <code>Bearer {API_KEY}</code></p>
            </div>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>brand_id</code></b>&nbsp;&nbsp; <small>integer</small>&nbsp; &nbsp; <input type="number" style="display: none" step="any" name="brand_id" data-endpoint="POSTapi-v1-samw-sign-in" value="12" data-component="body"><br>
              <p>법인코드. <br>예시: <code>12</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>user_name</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="user_name" data-endpoint="POSTapi-v1-samw-sign-in" value="test0001" data-component="body"><br>
              <p>유저 ID. <br>예시: <code>test0001</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>user_pw</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="user_pw" data-endpoint="POSTapi-v1-samw-sign-in" value="test0001" data-component="body"><br>
              <p>패스워드. <br>예시: <code>test0001</code></p>
            </div>
          </form>
          <h3>응답</h3>
          <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
          <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>access_token</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <br>
            <p>Bearer 토큰 값</p>
          </div>
          <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp; <small>object</small>&nbsp; &nbsp; <br></div>
          <h2 id="samw-api-GETapi-v1-samw-withdraws-balance">출금가능금액 조회</h2>
          <p><small class="badge badge-darkred">인증 필요</small></p>
          <p>출금가능한금액을 조회합니다.<br>즉시 출금 결제모듈의 매출은 포함되지 않습니다.</p><span id="example-requests-GETapi-v1-samw-withdraws-balance">
            <blockquote>예시 요청:</blockquote>
            <div class="javascript-example">
              <pre><code class="language-javascript">const url = new URL(
    "https://www.routeup.kr/api/v1/samw/withdraws/balance"
);

const params = {
    "samw_code": "2BWHVKQS7P",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {ACCESS_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "External-Api": "Bearer {API_KEY}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
            </div>
            <div class="php-example">
              <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;get(
    'https://www.routeup.kr/api/v1/samw/withdraws/balance',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'External-Api' =&gt; 'Bearer {API_KEY}',
        ],
        'query' =&gt; [
            'samw_code' =&gt; '2BWHVKQS7P',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
            </div>
            <div class="python-example">
              <pre><code class="language-python">import requests
import json

url = 'https://www.routeup.kr/api/v1/samw/withdraws/balance'
params = {
  'samw_code': '2BWHVKQS7P',
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'External-Api': 'Bearer {API_KEY}'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
            </div>
            <div class="bash-example">
              <pre><code class="language-bash">curl --request GET \
    --get "https://www.routeup.kr/api/v1/samw/withdraws/balance?samw_code=2BWHVKQS7P" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "External-Api: Bearer {API_KEY}"</code></pre>
            </div>
          </span><span id="example-responses-GETapi-v1-samw-withdraws-balance">
            <blockquote>
              <p>예시 응답 (200):</p>
            </blockquote>
            <pre><code class="language-json">{
    &quot;profit&quot;: 412320,
    &quot;withdraw_fee&quot;: 1000
}</code></pre>
          </span><span id="execution-results-GETapi-v1-samw-withdraws-balance" hidden>
            <blockquote>받은 응답<span id="execution-response-status-GETapi-v1-samw-withdraws-balance"></span>: </blockquote>
            <pre class="json"><code id="execution-response-content-GETapi-v1-samw-withdraws-balance"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
          </span><span id="execution-error-GETapi-v1-samw-withdraws-balance" hidden>
            <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
            <pre><code id="execution-error-message-GETapi-v1-samw-withdraws-balance">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
          </span>
          <form id="form-GETapi-v1-samw-withdraws-balance" data-method="GET" data-path="api/v1/samw/withdraws/balance" data-authed="1" data-hasfiles="0" data-isarraybody="0" autocomplete="off" onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-samw-withdraws-balance', this);">
            <h3> 요청&nbsp;&nbsp;&nbsp; <button type="button" style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-GETapi-v1-samw-withdraws-balance" onclick="tryItOut('GETapi-v1-samw-withdraws-balance');">시도하기 ⚡ </button><button type="button" style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-GETapi-v1-samw-withdraws-balance" onclick="cancelTryOut('GETapi-v1-samw-withdraws-balance');" hidden>취소 🛑 </button>&nbsp;&nbsp; <button type="submit" style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-GETapi-v1-samw-withdraws-balance" data-initial-text="요청 💥" data-loading-text="⏱ 요청중..." hidden>요청 💥 </button></h3>
            <p><small class="badge badge-green">GET</small><b><code>api/v1/samw/withdraws/balance</code></b></p>
            <h4 class="fancy-heading-panel"><b>헤더</b></h4>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Authorization" class="auth-value" data-endpoint="GETapi-v1-samw-withdraws-balance" value="Bearer {ACCESS_TOKEN}" data-component="header"><br>
              <p><br>예시: <code>Bearer {ACCESS_TOKEN}</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Content-Type" data-endpoint="GETapi-v1-samw-withdraws-balance" value="application/json" data-component="header"><br>
              <p><br>예시: <code>application/json</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Accept" data-endpoint="GETapi-v1-samw-withdraws-balance" value="application/json" data-component="header"><br>
              <p><br>예시: <code>application/json</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>External-Api</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="External-Api" data-endpoint="GETapi-v1-samw-withdraws-balance" value="Bearer {API_KEY}" data-component="header"><br>
              <p><br>예시: <code>Bearer {API_KEY}</code></p>
            </div>
            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>samw_code</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="samw_code" data-endpoint="GETapi-v1-samw-withdraws-balance" value="2BWHVKQS7P" data-component="query"><br>
              <p>SAMW CODE <br>예시: <code>2BWHVKQS7P</code></p>
            </div>
          </form>
          <h3>응답</h3>
          <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
          <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>profit</code></b>&nbsp;&nbsp; <small>integer</small>&nbsp; &nbsp; <br></div>
          <h2 id="samw-api-POSTapi-v1-samw-withdraws">출금요청</h2>
          <p><small class="badge badge-darkred">인증 필요</small></p>
          <p>출금가능한금액을 조회합니다.<br>암호화 예시: base64_encode(openssl_encrypt(a, &quot;AES-256-CBC&quot;, enc_key, true, iv))</p><span id="example-requests-POSTapi-v1-samw-withdraws">
            <blockquote>예시 요청:</blockquote>
            <div class="javascript-example">
              <pre><code class="language-javascript">const url = new URL(
    "https://www.routeup.kr/api/v1/samw/withdraws"
);

const headers = {
    "Authorization": "Bearer {ACCESS_TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "External-Api": "Bearer {API_KEY}",
};

let body = {
    "samw_code": "2BWHVKQS7P",
    "withdraw_amount": 1000,
    "acct_num": "141020101231321",
    "acct_name": "홍길동",
    "acct_bank_code": "기업은행",
    "acct_bank_name": "003"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
            </div>
            <div class="php-example">
              <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$response = $client-&gt;post(
    'https://www.routeup.kr/api/v1/samw/withdraws',
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {ACCESS_TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'External-Api' =&gt; 'Bearer {API_KEY}',
        ],
        'json' =&gt; [
            'samw_code' =&gt; '2BWHVKQS7P',
            'withdraw_amount' =&gt; 1000,
            'acct_num' =&gt; '141020101231321',
            'acct_name' =&gt; '홍길동',
            'acct_bank_code' =&gt; '기업은행',
            'acct_bank_name' =&gt; '003',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
            </div>
            <div class="python-example">
              <pre><code class="language-python">import requests
import json

url = 'https://www.routeup.kr/api/v1/samw/withdraws'
payload = {
    "samw_code": "2BWHVKQS7P",
    "withdraw_amount": 1000,
    "acct_num": "141020101231321",
    "acct_name": "홍길동",
    "acct_bank_code": "기업은행",
    "acct_bank_name": "003"
}
headers = {
  'Authorization': 'Bearer {ACCESS_TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'External-Api': 'Bearer {API_KEY}'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>
            </div>
            <div class="bash-example">
              <pre><code class="language-bash">curl --request POST \
    "https://www.routeup.kr/api/v1/samw/withdraws" \
    --header "Authorization: Bearer {ACCESS_TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "External-Api: Bearer {API_KEY}" \
    --data "{
    \"samw_code\": \"2BWHVKQS7P\",
    \"withdraw_amount\": 1000,
    \"acct_num\": \"141020101231321\",
    \"acct_name\": \"홍길동\",
    \"acct_bank_code\": \"기업은행\",
    \"acct_bank_name\": \"003\"
}"
</code></pre>
            </div>
          </span><span id="example-responses-POSTapi-v1-samw-withdraws">
            <blockquote>
              <p>예시 응답 (201):</p>
            </blockquote>
            <pre><code class="language-json">{
    &quot;id&quot;: 123
}</code></pre>
          </span><span id="execution-results-POSTapi-v1-samw-withdraws" hidden>
            <blockquote>받은 응답<span id="execution-response-status-POSTapi-v1-samw-withdraws"></span>: </blockquote>
            <pre class="json"><code id="execution-response-content-POSTapi-v1-samw-withdraws"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
          </span><span id="execution-error-POSTapi-v1-samw-withdraws" hidden>
            <blockquote>오류로 인해 요청이 실패했습니다.:</blockquote>
            <pre><code id="execution-error-message-POSTapi-v1-samw-withdraws">

팁: 네트워크에 제대로 연결되어 있는지 확인하세요.
해당 API의 관리자인 경우 API가 실행 중이고 CORS를 활성화했는지 확인하세요.
디버깅 정보는 개발자 도구 콘솔에서 확인할 수 있습니다.</code></pre>
          </span>
          <form id="form-POSTapi-v1-samw-withdraws" data-method="POST" data-path="api/v1/samw/withdraws" data-authed="1" data-hasfiles="0" data-isarraybody="0" autocomplete="off" onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-samw-withdraws', this);">
            <h3> 요청&nbsp;&nbsp;&nbsp; <button type="button" style="border-color: #2196f3; background-color: #2196f3; color:white;padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-tryout-POSTapi-v1-samw-withdraws" onclick="tryItOut('POSTapi-v1-samw-withdraws');">시도하기 ⚡ </button><button type="button" style="border-color: #dfa1a5; background-color: #dfa1a5; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-canceltryout-POSTapi-v1-samw-withdraws" onclick="cancelTryOut('POSTapi-v1-samw-withdraws');" hidden>취소 🛑 </button>&nbsp;&nbsp; <button type="submit" style="border-color: #81f18e; background-color: #81f18e; padding: 5px 10px; border-radius: 5px; border-width: thin;" id="btn-executetryout-POSTapi-v1-samw-withdraws" data-initial-text="요청 💥" data-loading-text="⏱ 요청중..." hidden>요청 💥 </button></h3>
            <p><small class="badge badge-black">POST</small><b><code>api/v1/samw/withdraws</code></b></p>
            <h4 class="fancy-heading-panel"><b>헤더</b></h4>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-samw-withdraws" value="Bearer {ACCESS_TOKEN}" data-component="header"><br>
              <p><br>예시: <code>Bearer {ACCESS_TOKEN}</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Content-Type" data-endpoint="POSTapi-v1-samw-withdraws" value="application/json" data-component="header"><br>
              <p><br>예시: <code>application/json</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="Accept" data-endpoint="POSTapi-v1-samw-withdraws" value="application/json" data-component="header"><br>
              <p><br>예시: <code>application/json</code></p>
            </div>
            <div style="padding-left: 28px; clear: unset;"><b style="line-height: 2;"><code>External-Api</code></b>&nbsp;&nbsp; &nbsp; &nbsp; <input type="text" style="display: none" name="External-Api" data-endpoint="POSTapi-v1-samw-withdraws" value="Bearer {API_KEY}" data-component="header"><br>
              <p><br>예시: <code>Bearer {API_KEY}</code></p>
            </div>
            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>samw_code</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="samw_code" data-endpoint="POSTapi-v1-samw-withdraws" value="2BWHVKQS7P" data-component="body"><br>
              <p>SAMW CODE. <br>예시: <code>2BWHVKQS7P</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>withdraw_amount</code></b>&nbsp;&nbsp; <small>integer</small>&nbsp; &nbsp; <input type="number" style="display: none" step="any" name="withdraw_amount" data-endpoint="POSTapi-v1-samw-withdraws" value="1000" data-component="body"><br>
              <p>출금요청할 금액.<br>출금가능금액을 초과할 수 없습니다. <br>예시: <code>1000</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>acct_num</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="acct_num" data-endpoint="POSTapi-v1-samw-withdraws" value="141020101231321" data-component="body"><br>
              <p>입금 계좌번호.<br>(AES-256-CBC 암호화 필요). <br>예시: <code>141020101231321</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>acct_name</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="acct_name" data-endpoint="POSTapi-v1-samw-withdraws" value="홍길동" data-component="body"><br>
              <p>예금주명.<br>(AES-256-CBC 암호화 필요). <br>예시: <code>홍길동</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>acct_bank_code</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="acct_bank_code" data-endpoint="POSTapi-v1-samw-withdraws" value="기업은행" data-component="body"><br>
              <p>입금 은행코드.<br>(AES-256-CBC 암호화 필요). <br>예시: <code>기업은행</code></p>
            </div>
            <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>acct_bank_name</code></b>&nbsp;&nbsp; <small>string</small>&nbsp; &nbsp; <input type="text" style="display: none" name="acct_bank_name" data-endpoint="POSTapi-v1-samw-withdraws" value="003" data-component="body"><br>
              <p>입금 은행명.<br>(AES-256-CBC 암호화 필요). <br>예시: <code>003</code></p>
            </div>
          </form>
          <h3>응답</h3>
          <h4 class="fancy-heading-panel"><b>응답 필드</b></h4>
          <div style=" padding-left: 28px;  clear: unset;"><b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp; <small>integer</small>&nbsp; &nbsp; <br>
            <p>출금요청 고유번호</p>
          </div>
      </div>
      <div class="dark-box">
        <div class="lang-selector"><button type="button" class="lang-button" data-language-name="javascript">javascript</button><button type="button" class="lang-button" data-language-name="php">php</button><button type="button" class="lang-button" data-language-name="python">python</button><button type="button" class="lang-button" data-language-name="bash">bash</button></div>
      </div>
    </div>
  </body>
</html>
