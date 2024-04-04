# 소개

본 문서는 PAYVERY와 BF간 인터페이스에 대하여 기술합니다.

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

