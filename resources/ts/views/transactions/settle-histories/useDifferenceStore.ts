import { Header } from '@/views/headers';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { DifferentSettlementInfo, Transaction } from '@/views/types';
import { getUserLevel } from '@axios';


const hecto = () => {
    return `<div>
    <h2>헥토파이낸셜</h2>
    <br>
    <h3>1.1 차액 정산 송/수신 주기</h3>
     차액 정산 요청 파일 
    <br>
    - 거래 일자(D) 기준, D+1일 13시 전까지 업로드 (365일 전송) <br><br>
     차액 정산 결과 파일 <br>
    - 차액 정산 요청 일자 기준 영업일 +7일 04시 업로드 <br><br>
    Ex) 거래일자(D) : 2023년 01월 07일(토) <br>
    차액 정산 요청 파일 ( 가맹점 -> 헥토파이낸셜 ) : 2023년 01월 08일(일) <br>
    차액 정산 결과 파일 ( 헥토파이낸셜 -> 가맹점 ) : 2023년 01월 17일(화) <br>
    <br>
    <h3>1.2 차액 정산 전송 대상 </h3>
     전송 대상 <br>
    - 22년 하반기(9월)부터 시행된 환급정산 정책에 의해 일반(4) 매출 구간 차액 정산 데이터 전송 必 <br>
    - 장바구니 매출 발생시 PG거래번호 당 최종 하위 사업자 번호 중복 불가 <br>
    - 장바구니 매출에 대한 차액 정산 데이터 전송 시 최종 하위 사업자 매출 전부 포함 필수( PG 거래번호 기준 거래 금액과 최종 하위 사업자 매출 금액의 합계가 일치하여야 함 )<br>
    <br>
    <h3>1.3 환급 정산 </h3>
     개요 <br>
    - 22년 9월 첫 시행 ( 22년 상반기 매출 ) <br>
    - 신규 사업자의 경우 일반(4)사업자로 선정이 되어 반기 동안 영중소 우대수수료 적용 받지 못한 매출에 대해 수수료 환급 정산 진행( 환급 대상 사업자 리스트는 여신 협회에서 수신 ) <br>
    Ex). 2월 개업 후 7월에 영세 우대 수수료 적용 시 2월~7월까지 차액 수수료(일반-영세) 환급 <br>
     환급 정산 방식 <br>
    - 가맹점(2차PG사) -> 당사로 별도의 환급 정산 요청 파일 전송 없음 <br>
    - 기 전송된 차액 정산 매출 데이터로 카드사에 환급 정산 요청 ( 헥토파이낸셜 자체 처리 ) <br>
    ( 차액 정산 반송 된 건에 대해서는 환급 정산 처리 불가하므로 차액 정산 반송 관리 必 ) <br>
    <br>
    <h3>1.4 차액정산 결과 코드(헥토파이낸셜 검증 결과 코드)</h3>
    오류코드 오류메시지<br>
    <div class="v-table v-theme--light v-table--density-default text-no-wrap">
        <div class="v-table__wrapper different-settle-menual">
            <table>
                <thead>
                    <tr>
                        <th class='list-square'>코드</th>
                        <th class='list-square'>응답 메세지</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class='list-square'>0000</td>
                        <td class='list-square'>정상</td>
                    </tr>
                    <tr>
                        <td class='list-square'>1101</td>
                        <td class='list-square'>상점ID 미 존재</td>
                    </tr>
                    <tr>
                        <td class='list-square'>1102</td>
                        <td class='list-square'>차액 정산 비대상 가맹점</td>
                    </tr>
                    <tr>
                        <td class='list-square'>1103</td>
                        <td class='list-square'>영중소 사업자번호 아님</td>
                    </tr>
                    <tr>
                        <td class='list-square'>1201</td>
                        <td class='list-square'>해당거래 미 존재</td>
                    </tr>
                    <tr>
                        <td class='list-square'>1202</td>
                        <td class='list-square'>원거래 매입금액 불일치</td>
                    </tr>
                    <tr>
                        <td class='list-square'>1203</td>
                        <td class='list-square'>원거래 매입금액과 하위사업자 매출액 불일치 </td>
                    </tr>
                    <tr>
                        <td class='list-square'>1301</td>
                        <td class='list-square'>매입전 취소 거래 </td>
                    </tr>
                    <tr>
                        <td class='list-square'>1302</td>
                        <td class='list-square'>당일 취소 거래</td>
                    </tr> 
                    <tr>
                        <td class='list-square'>1401</td>
                        <td class='list-square'>차액 정산 매입결과 미수신</td>
                    </tr> 
                    <tr>
                        <td class='list-square'>9999</td>
                        <td class='list-square'>기타오류 (PG사 문의) (ex. 중복 전송, 최종하위사업자번호 오류 등. ) </td>
                    </tr>    
                </tbody>
            </table>
        </div>
    </div>
    <h3>1.5 카드사 결과 코드</h3>
    오류코드 오류메시지<br>
    <div class="v-table v-theme--light v-table--density-default text-no-wrap">
    <div class="v-table__wrapper different-settle-menual">
        <table>
            <thead>
                <tr>
                    <th class='list-square'>코드</th>
                    <th class='list-square'>응답 메세지</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='list-square'>00</td>
                    <td class='list-square'>정상</td>
                </tr>
                <tr>
                    <td class='list-square'>01</td>
                    <td class='list-square'>카드사별 구분값 오류(미존재 또는 불일치)</td>
                </tr>
                <tr>
                    <td class='list-square'>02</td>
                    <td class='list-square'>매출금액 오류(원매출금액과 하위사업자 매출액 SUM의 불일치) </td>
                </tr>
                <tr>
                    <td class='list-square'>03</td>
                    <td class='list-square'>중복접수(기 처리된 내역을 전송)</td>
                </tr>
                <tr>
                    <td class='list-square'>04</td>
                    <td class='list-square'>원매입 반송(원매출 미존재 또는 매출금액 오류 등) </td>
                </tr>
                <tr>
                    <td class='list-square'>05</td>
                    <td class='list-square'>매입취소구분 오류(원매출과 하위매출의 정상/취소 불일치)</td>
                </tr>
                <tr>
                    <td class='list-square'>06</td>
                    <td class='list-square'>매입전송일자 오류</td>
                </tr>         
                <tr>
                    <td class='list-square'>07</td>
                    <td class='list-square'>승인일자 오류</td>
                </tr>
                <tr>
                    <td class='list-square'>08</td>
                    <td class='list-square'>승인번호 오류(원승인번호에 해당하는 매출 미존재</td>
                </tr> 
                <tr>
                    <td class='list-square'>09</td>
                    <td class='list-square'>가맹점번호 오류1(가맹점번호가 SPACE이거나 미등록가맹점) </td>
                </tr> 
                <tr>
                    <td class='list-square'>10</td>
                    <td class='list-square'>가맹점번호 오류2(차액정산 가맹점 번호가 아님) </td>
                </tr>          
                <tr>
                    <td class='list-square'>11</td>
                    <td class='list-square'>카드번호 오류</td>
                </tr>
                <tr>
                    <td class='list-square'>12</td>
                    <td class='list-square'>중간하위사업자 오류(전자금융업자 미해당사업자 등)</td>
                </tr>      
                <tr>
                    <td class='list-square'>13</td>
                    <td class='list-square'>차액정산 지연접수</td>
                </tr>
                <tr>
                    <td class='list-square'>14</td>
                    <td class='list-square'>카드사별 구분값 정상건 반송<br>
                    (A,B,C로 구성된 장바구니 거래에서 C의 카드사별 구분값이 오류인 경우 C는 01번 코드로 회신
                    하나,<br>A,B는 카드사별 구분값이 정상임에도 C로 인해 반송되는 것이므로 14번 코드로 구별하여 
                    회신)</td>
                </tr>
                <tr>
                    <td class='list-square'>15</td>
                    <td class='list-square'>매출이 전체 취소된 이후, +차액정산 접수시 반송<br>
                    (반송조건 추가 사유 : -차액정산(취소) 접수가 지연 되는 케이스 막고자 함)</td>
                </tr>
                <tr>
                    <td class='list-square'>99</td>
                    <td class='list-square'>기타</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
    <br>
    <h3>1.6 진행 순서</h3>
    1. 차액 정산 진행 <br>
    2. 반기 단위로 직전 반기에 전송된 차액 정산 일반 매출 구간 사업자 중 환급 대상 사업자의 매출 내역을 환급 정산 요청 ( 헥토파이낸셜(자체처리) -> 카드사 ) <br>
    3. 환급 정산 지급 ( 별도의 결과 파일 제공 되지 않으며 정산 내역으로 확인 필요 )<br>
    </div>
    <br>`
}

const welcome = () => {
    return `<div>
    <h2>웰컴페이먼츠</h2>
    <br>
    <h3>1.1 차액 정산 송/수신 주기</h3>
     차액 정산 요청 파일 
    <br>
    - 거래 일자(D) 기준, D+1일 07시 전까지 업로드 (365일 전송) <br><br>
     차액 정산 결과 파일 <br>
    - 차액 정산 요청 일자 기준 영업일 D+5일 07시 업로드 <br><br>
    Ex) 거래일자(D) : 2023년 01월 07일(토) <br>
    차액 정산 요청 파일 ( 가맹점 -> 웰컴페이먼츠 ) : 2023년 01월 08일(일)<br>
    차액 정산 결과 파일 ( 웰컴페이먼츠 -> 가맹점 ) : 2023년 01월 14일(금)<br>
    <br>
    <h3>1.2 차액정산 프로세스</h3>
    차액정산 결과
    <div class="v-table v-theme--light v-table--density-default text-no-wrap">
        <div class="v-table__wrapper different-settle-menual">
            <table>
                <thead>
                    <tr>
                        <th class='list-square'>D+</th>
                        <th class='list-square'>내용</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style='text-align:left'>D+0</th>
                        <td class='list-square'>승인일</td>
                    </tr>
                    <tr>
                        <th style='text-align:left'>D+1</th>
                        <td class='list-square'>가맹점차액정산 요청</td>
                    </tr>
                    <tr>
                        <th style='text-align:left'>D+2~D+4</th>
                        <td class='list-square'>카드사 <-> 웰컴페이먼츠 매입, 차액정산 송수신</td>
                    </tr>
                    <tr>    
                        <th style='text-align:left'>D+5</th>
                        <td class='list-square'>웰컴페이먼츠 차액결과 수신/가맹점 결과파일 생성</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br>
    <h3>1.3 차액정산 결과 코드(웰컴페이먼츠 결과 코드)</h3>
    오류코드 오류메시지<br>
    <div class="v-table v-theme--light v-table--density-default text-no-wrap">
        <div class="v-table__wrapper different-settle-menual">
            <table>
                <thead>
                    <tr>
                        <th class='list-square'>코드</th>
                        <th class='list-square'>응답 메세지</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td class='list-square'>01</td>
                    <td class='list-square'>카드사별 구분값 오류(미존재 또는 불일치)</td>
                </tr>
                <tr>
                    <td class='list-square'>02</td>
                    <td class='list-square'>매출금액 오류(원매출금액과 하위사업자 매출액 SUM의 불일치) </td>
                </tr>
                <tr>
                    <td class='list-square'>03</td>
                    <td class='list-square'>중복접수(기 처리된 내역을 전송)</td>
                </tr>
                <tr>
                    <td class='list-square'>04</td>
                    <td class='list-square'>원매입 반송(원매출 미존재 또는 매출금액 오류 등) </td>
                </tr>
                <tr>
                    <td class='list-square'>05</td>
                    <td class='list-square'>매입취소구분 오류(원매출과 하위매출의 정상/취소 불일치)</td>
                </tr>
                <tr>
                    <td class='list-square'>06</td>
                    <td class='list-square'>매입전송일자 오류</td>
                </tr>         
                <tr>
                    <td class='list-square'>07</td>
                    <td class='list-square'>승인일자 오류</td>
                </tr>
                <tr>
                    <td class='list-square'>08</td>
                    <td class='list-square'>승인번호 오류(원승인번호에 해당하는 매출 미존재</td>
                </tr> 
                <tr>
                    <td class='list-square'>09</td>
                    <td class='list-square'>가맹점번호 오류1(가맹점번호가 SPACE이거나 미등록가맹점) </td>
                </tr> 
                <tr>
                    <td class='list-square'>10</td>
                    <td class='list-square'>가맹점번호 오류2(차액정산 가맹점 번호가 아님) </td>
                </tr>          
                <tr>
                    <td class='list-square'>11</td>
                    <td class='list-square'>카드번호 오류</td>
                </tr>
                <tr>
                    <td class='list-square'>12</td>
                    <td class='list-square'>중간하위사업자 오류(전자금융업자 미해당사업자 등)</td>
                </tr>      
                <tr>
                    <td class='list-square'>13</td>
                    <td class='list-square'>차액정산 지연접수</td>
                </tr>
                <tr>
                    <td class='list-square'>14</td>
                    <td class='list-square'>카드사별 구분값 정상건 반송<br>
                    (A,B,C로 구성된 장바구니 거래에서 C의 카드사별 구분값이 오류인 경우 C는 01번 코드로 회신
                    하나,<br>A,B는 카드사별 구분값이 정상임에도 C로 인해 반송되는 것이므로 14번 코드로 구별하여 
                    회신) </td>
                </tr>
                <tr>
                    <td class='list-square'>15</td>
                    <td class='list-square'>매출이 전체 취소된 이후, +차액정산 접수시 반송<br>
                    (반송조건 추가 사유 : -차액정산(취소) 접수가 지연 되는 케이스 막고자 함)</td>
                </tr>
                <tr>
                    <td class='list-square'>98</td>
                    <td class='list-square'>롯데카드 기타 오류 중 기 승인/취소 완료된 거래</td>
                </tr>
                <tr>
                    <td class='list-square'>99</td>
                    <td class='list-square'>기타</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div><br>`
}

const danal = () => {
    return `<div>
    <h2>다날</h2>
    <br>
    <h3>1.1 차액 정산 송/수신 주기</h3>
     차액 정산 요청 파일 
    <br>
    - 거래 일자(D) 기준, D+1일 09시 전까지 업로드 (365일 전송) <br><br>
     차액 정산 결과 파일 <br>
    - 차액 정산 요청 일자 기준 영업일 D+7일 07시 업로드 <br><br>
    Ex) 거래일자(D) : 2023년 01월 07일(토) <br>
    차액 정산 요청 파일 ( 가맹점 -> 다날 ) : 2023년 01월 08일(일) <br>
    차액 정산 결과 파일 ( 다날 -> 가맹점 ) : 2023년 01월 17일(화) <br>
    <br>
    <h3>1.2 차액정산 결과 코드(다날 결과 코드)</h3>
    오류코드 오류메시지<br>
    <div class="v-table v-theme--light v-table--density-default text-no-wrap">
        <div class="v-table__wrapper different-settle-menual">
            <table>
                <thead>
                    <tr>
                        <th class='list-square'>코드</th>
                        <th class='list-square'>응답 메세지</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class='list-square'>00</td>
                        <td class='list-square'>정상</td>
                    </tr>
                    <tr>
                        <td class='list-square'>01</td>
                        <td class='list-square'>매출정보 오류</td>
                    </tr>
                    <tr>
                        <td class='list-square'>02</td>
                        <td class='list-square'>정보 오류 (사업자번호, 가맹점번호 불일치 등)</td>
                    </tr>
                    <tr>
                        <td class='list-square'>99</td>
                        <td class='list-square'>기타 오류</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>`
}

const galaxiamoneytree = () => {
    return `<div>
    <h2>갤럭시아머니트리</h2>
    <br>
    <h3>1.1 차액 정산 송/수신 주기</h3>
     차액 정산 요청 파일 
    <br>
    - 거래 일자(D) 기준, D+1일 09시 전까지 업로드 (365일 전송) <br><br>
     차액 정산 결과 파일 <br>
    - 차액 정산 요청 일자 기준 영업일 D+5일 07시 업로드 <br><br>
    Ex) 거래일자(D) : 2023년 01월 07일(토) <br>
    차액 정산 요청 파일 ( 가맹점 -> 갤럭시아머니트리 ) : 2023년 01월 08일(일) <br>
    차액 정산 결과 파일 ( 갤럭시아머니트리 -> 가맹점 ) : 2023년 01월 13일(금) <br>
    <br>
    <h3>1.2 차액정산 결과 코드(갤럭시아머니트리 결과 코드)</h3>
    오류코드 오류메시지<br>
    <div class="v-table v-theme--light v-table--density-default text-no-wrap">
        <div class="v-table__wrapper different-settle-menual">
            <table>
                <thead>
                    <tr>
                        <th class='list-square'>코드</th>
                        <th class='list-square'>응답 메세지</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class='list-square'>00</td>
                        <td class='list-square'>정상</td>
                    </tr>
                    <tr>
                        <td class='list-square'>01</td>
                        <td class='list-square'>승인취소 거래건</td>
                    </tr>
                    <tr>
                        <td class='list-square'>02</td>
                        <td class='list-square'>매출금액 오류</td>
                    </tr>
                    <tr>
                        <td class='list-square'>03</td>
                        <td class='list-square'>중복 접수</td>
                    </tr>
                    <tr>
                        <td class='list-square'>04</td>
                        <td class='list-square'>원거래 매입 거절건</td>
                    </tr>
                    <tr>
                        <td class='list-square'>05</td>
                        <td class='list-square'>매입취소구분 오류</td>
                    </tr>
                    <tr>
                        <td class='list-square'>06</td>
                        <td class='list-square'>일반사업자</td>
                    </tr>
                    <tr>
                        <td class='list-square'>07</td>
                        <td class='list-square'>차액정산 지연접수자</td>
                    </tr>
                    <tr>
                        <td class='list-square'>08</td>
                        <td class='list-square'>원거래 없음</td>
                    </tr>
                    <tr>
                        <td class='list-square'>09</td>
                        <td class='list-square'>차액정산 대상아님</td>
                    </tr>
                    <tr>
                        <td class='list-square'>99</td>
                        <td class='list-square'>기타 오류</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>`
}

export const getDifferenceSettleMenual = (different_settlement_infos: DifferentSettlementInfo[]) => {
    let html = '';
    for (let i = 0; i < different_settlement_infos.length; i++) 
    {
        if(different_settlement_infos[i].pg_type === 5)
            html += hecto()
        else if(different_settlement_infos[i].pg_type === 30)
            html += welcome()
        else if(different_settlement_infos[i].pg_type === 22)
            html += danal()
        else if(different_settlement_infos[i].pg_type === 28)
            html += galaxiamoneytree()
    }
    return html
}

export const useSearchStore = defineStore('transSettlesHistoryDifferenceSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/difference')
    const head  = Header('transactions/settle-histories/difference', '차액정산 이력')
    const headers: Record<string, string> = {
        'id': 'NO.',
        'settle_result_msg': '정산 결과',
        'card_company_result_msg': '카드사 결과',
        'module_type': '거래 타입',
    }
    const { pgs, pss, terminals } = useStore()
    if(getUserLevel() >= 35) {
        headers['pg_id'] = 'PG사'
        headers['ps_id'] = '구간'
        headers['ps_fee'] = '구간 수수료'
        headers['mcht_section_name'] = '가맹점 구간'
        headers['settle_dt'] = '정산 예정일'
    }
    headers['mcht_name'] = '가맹점'
    headers['amount'] = '거래금액'
    headers['supply_amount'] = '공급가액'
    headers['vat_amount'] = '부가세'
    headers['settle_amount'] = '차액 정산금'

    headers['trx_dttm'] = '거래 시간'
    headers['cxl_dttm'] = '취소 시간'
    headers['installment'] = '할부'
    if(getUserLevel() >= 13) {
        headers['mid'] = 'MID'
        headers['tid'] = 'TID'    
    }
    if(getUserLevel() >= 35) {
        headers['custom_id'] = '커스텀필터'
        headers['terminal_id'] = '장비타입'
    }
    headers['appr_num'] = '승인번호'    
    headers['issuer'] = '발급사'
    headers['acquirer'] = '매입사'

    headers['card_num'] = '카드번호'
    headers['buyer_name'] = '구매자명'
    headers['buyer_phone'] = '구매자 연락처'
    
    headers['item_name'] = '상품명'
    if(getUserLevel() >= 13)
    {
        headers['ord_num'] = '주문번호'
        headers['trx_id'] = '거래번호'
    }    
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'
    
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const metas = ref([
        {
            icon: 'ic-outline-payments',
            color: 'primary',
            title: '요청 거래액 합계',
            percentage: 0,
            stats: '0',
        },
        {
            icon: 'ic-outline-payments',
            color: 'error',
            title: '부가세 합계',
            percentage: 0,
            stats: '0',
        },
        {
            icon: 'ic-outline-payments',
            color: 'success',
            title: '공급가액 합계',
            percentage: 0,
            stats: '0',
        },
        {
            icon: 'ic-outline-payments',
            color: 'warning',
            title: '차액정산금 합계',
            percentage: 0,
            stats: '0',
        },
    ])

    const exporter = async (type: number) => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(type, r.data.content)
    }
    const printer = (type:number, datas: Transaction[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }

    return {
        store,
        head,
        exporter,
        printer,
        metas,
    }
})
