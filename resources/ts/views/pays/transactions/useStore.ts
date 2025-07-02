
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { Options } from '@/views/types'

export const installments = <Options[]>([
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },
])

export const useSearchStore = defineStore('useTransactionSearchStore', () => {
    const store = Searcher('pays/transactions')
    const head = Header('pays/transactions', '거래내역')

    const getIdCols = () => {
        return {
            'id' : 'NO.',
        }
    }

    const getPaymentModuleCols = () => {
        return {
            'note': '결제모듈 별칭',
            'module_type': '거래타입',
            'mid': 'MID',
            'tid': 'TID',
        }
    }
    
    const getTransactionCols = () => {
        return {
            'trx_status': '거래상태',
            'trx_at': '거래시간',
            'is_cancel': '거래타입',
            'amount': '거래금액',
            'installment': '할부',
            'appr_num': '승인번호',
        }
    }

    const getCardCols = () => {
        return {
            'card_num': '카드번호',
            'issuer': '발급사',
            'acquirer' : '매입사',         
        }
    }
    
    const getDepositCols = () => {
        return {
            'deposit_status': '이체상태',
        }        
    }

    const geETcCols = () => {
        return {
            'extra_col': '추가작업',
        }        
    }

    const headers0:any = getIdCols()
    const headers1:any = getPaymentModuleCols()
    const headers2:any = getTransactionCols()
    const headers3:any = getCardCols()
    const headers4:any = getDepositCols()
    const headers5:any = geETcCols()

    const headers: Record<string, string> = {
        ...headers0,
        ...headers1,
        ...headers2,
        ...headers3,
        ...headers4,
        ...headers5,
    }

    const sub_headers: any = []
    head.getSubHeaderCol('선택/취소', headers0, sub_headers)
    head.getSubHeaderCol('결제모듈 정보', headers1, sub_headers)
    head.getSubHeaderCol('결제 정보', headers2, sub_headers)
    head.getSubHeaderCol('결제 수단', headers3, sub_headers)
    head.getSubHeaderCol('이체 정보', headers4, sub_headers)
    head.getSubHeaderCol('더보기', headers5, sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }

    const metas = ref([])
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})
