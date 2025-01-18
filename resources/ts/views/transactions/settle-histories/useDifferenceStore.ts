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
    <h3>1.4 진행 순서</h3>
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
</div>`
}

export const getDifferenceSettleMenual = (different_settlement_infos: DifferentSettlementInfo[]) => {
    let html = '';
    for (let i = 0; i < different_settlement_infos.length; i++) 
    {
        if(different_settlement_infos[i].pg_type === 5)
            html += hecto()
        else if(different_settlement_infos[i].pg_type === 13)
            html += seact9ine()
        else if(different_settlement_infos[i].pg_type === 30)
            html += welcome()
        else if(different_settlement_infos[i].pg_type === 22)
            html += danal()
        else if(different_settlement_infos[i].pg_type === 28)
            html += galaxiamoneytree()
        else if(different_settlement_infos[i].pg_type === 37)
            html += ksnet()

    }
    return html
}

export const getDifferenceSettlementResultCode = (settle_result_code: string) => {
    if(settle_result_code === '00' || settle_result_code === '0000')
        return 'success'
    else if(settle_result_code === '50')
        return 'primary'
    else if(settle_result_code === '51')
        return 'default'
    else
        return 'error';
}

export const getDifferenceSettlemenMchtCode = (mcht_settle_type: string) => {
    if(mcht_settle_type === '0')
        return 'success'
    else if(mcht_settle_type === '1')
        return 'info'
    else if(mcht_settle_type === '2')
        return 'primary'
    else if(mcht_settle_type === '3')
        return 'error'
    else if(mcht_settle_type === '4')
        return 'error'
    else
        return 'default';
}

export const status_codes = [
    {id: 0, title: '전체'},
    {id: 1, title: '성공'},
    {id: 2, title: '모든에러'},
    {id: 50, title: '업로드 완료'},
    {id: 51, title: '재업로드'},
    {id: -101, title: 'MID 누락'},
    {id: -100, title: '가맹점 사업자번호 오기입'},
]

export const mcht_settle_types = [
    {id: '0', title: '영세'},
    {id: '1', title: '중소1'},
    {id: '2', title: '중소2'},
    {id: '3', title: '중소3'},
    {id: '4', title: '일반'},
]

export const useSearchStore = defineStore('transSettlesHistoryDifferenceSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/difference')
    const head  = Header('transactions/settle-histories/difference', '차액정산 이력')
    const headers: Record<string, string> = {
        'id': 'NO.',
        'settle_result_msg': '정산 결과',
        'module_type': '거래 타입',
    }
    const { pgs, pss, terminals } = useStore()
    if(getUserLevel() >= 35) {
        headers['pg_id'] = 'PG사'
        headers['ps_id'] = '구간'
        headers['mcht_section_code'] = '가맹점 구간'
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

    const exporter = async () => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(r.data.content)
    }
    const printer = (datas: Transaction[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)        
    }

    return {
        store,
        head,
        exporter,
        printer,
        metas,
    }
})
