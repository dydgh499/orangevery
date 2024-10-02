import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'

export const deposit_statuses = [
    { id: null, title: '전체' },
    { id: 0, title: '미입금' },
    { id: 1, title: '입금완료' },
    { id: 2, title: '상계처리' },
]

export const useSearchStore = defineStore('transSettlesHistoryMchtSearchStore', () => {    
    const store = Searcher('transactions/settle-histories/merchandises')
    const head  = Header('transactions/settle-histories/merchandises', '가맹점 정산이력')

    const getMerchandiseCols = () => {
        return {
            'id': 'NO.',
            'user_name' : '가맹점 ID',
            'mcht_name' : '상호',
        }
    }

    const getApprovalCols = () => {
        const headers_2:Record<string, string> = {}
        headers_2['appr_amount'] = '승인액'
        headers_2['appr_count'] = '승인건수'
        return headers_2
    }

    const getCancelCols = () => {
        const headers_3:Record<string, string> = {}
        headers_3['cxl_amount'] = '취소액'
        headers_3['cxl_count'] = '취소건수'
        return headers_3
    }

    const getSalesCols = () => {
        return {
            'total_amount': '매출액',
            'trx_amount' : '거래 수수료',
            'settle_fee' : '입금 수수료',
        }
    }

    const getTerminalCols = () => {
        return {
            comm_settle_amount: '통신비',
            under_sales_amount: '매출미달 차감금',
            deduct_amount: '추가차감액',
        }
    }

    const getSettleCols = () => {
        const headers_6:Record<string, string> = {}
        headers_6['cancel_deposit_amount'] = '취소입금합계'
        headers_6['settle_amount'] = '정산액'
        if(corp.pv_options.paid.use_finance_van_deposit)
            headers_6['deposit_amount'] = '이체금액'
        headers_6['settle_dt'] = '정산일'
        headers_6['deposit_dt'] = '입금일'
        headers_6['deposit_status'] = '입금상태'
        return headers_6
    }

    const getPrivacyCols = () => {
        if(((getUserLevel() == 10 && !user_info.value.is_hide_account) || getUserLevel() >= 13)) {
            return {
                'acct_bank_name': '은행',
                'acct_bank_code': '은행코드',
                'acct_name': '예금주',
                'acct_num': '계좌번호',
            }
        }
        else
            return {}
    }

    const getEtcCols = () => {
        const headers_8:Record<string, string> = {}
        headers_8['created_at'] = '생성시간'    
        if(getUserLevel() >= 35)
            headers_8['extra_col'] = '더보기'
        return headers_8
    }
    
    const headers0:any = getMerchandiseCols()
    const headers1:any = getApprovalCols()
    const headers2:any = getCancelCols()
    const headers3:any = getSalesCols()
    const headers4:any = getTerminalCols()
    const headers5:any = getSettleCols()
    const headers6:any = getPrivacyCols()
    const headers7:any = getEtcCols()

    const headers:Record<string, string | object> = {
        ...headers0,
        ...headers1,
        ...headers2,
        ...headers3,
        ...headers4,
        ...headers5,
        ...headers6,
        ...headers7,
    }
    const sub_headers: any = []
    head.getSubHeaderCol('가맹점 정보', headers0, sub_headers)
    head.getSubHeaderCol('승인 정보', headers1, sub_headers)
    head.getSubHeaderCol('취소 정보', headers2, sub_headers)
    head.getSubHeaderCol('매출 정보', headers3, sub_headers)
    head.getSubHeaderCol('결제모듈 정보', headers4, sub_headers)
    head.getSubHeaderCol('정산 정보', headers5, sub_headers)
    head.getSubHeaderCol('개인 정보', headers6, sub_headers)
    head.getSubHeaderCol('기타 정보', headers7, sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {      
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    
    return {
        store,
        head,
        exporter,
    }
})
