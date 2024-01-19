import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { getUserLevel } from '@axios'
import corp from '@corp'

export const deposit_statuses = [
    { id: null, title: '전체' },
    { id: 0, title: '미입금' },
    { id: 1, title: '입금' },
]

export const useSearchStore = defineStore('transSettlesHistoryMchtSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/merchandises')
    const head  = Header('transactions/settle-histories/merchandises', '가맹점 정산이력')
    const headers_1:Record<string, string | object> = {
        'id': 'NO.',
        'user_name' : '가맹점 ID',
        'mcht_name' : '상호',
        'appr_amount': '승인액',
        'cxl_amount': '취소액',
        'total_amount': '매출액',
        'trx_amount': '거래 수수료',
        'settle_fee': '입금 수수료',
        'comm_settle_amount': '통신비',
        'under_sales_amount': '매출미달 차감금',
        'deduct_amount': '추가차감액',
    };
    if(corp.pv_options.paid.use_cancel_deposit)
        headers_1['cancel_deposit_amount'] = '취소입금합계'
    if(corp.pv_options.paid.use_collect_withdraw)
        headers_1['collect_withdraw_amount'] = '모아서 출금합계'

    const headers_2:Record<string, string | object> = {
        'settle_amount': '정산액'
    }
    const headers_3:Record<string, string | object> = {
        'settle_dt': '정산일',
        'deposit_dt': '입금일',
        'deposit_status': '입금상태',
        'acct_bank_name': '은행',
        'acct_bank_code': '은행코드',
        'acct_name': '예금주',
        'acct_num': '계좌번호',
        'created_at': '생성시간',
    }
    if(corp.pv_options.paid.use_finance_van_deposit)
        headers_2['deposit_amount'] = '이체금액'

    if(getUserLevel() >= 35)
        headers_3['extra_col'] = '더보기'
    
    const headers:Record<string, string | object> = {
        ...headers_1,
        ...headers_2,
        ...headers_3,
    }
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {      
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    
    return {
        store,
        head,
        exporter,
    }
})
