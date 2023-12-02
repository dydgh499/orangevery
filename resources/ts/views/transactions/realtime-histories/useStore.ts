import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { RealtimeHistory } from '@/views/types'

export const useSearchStore = defineStore('realtimeHistoriesSearchStore', () => {    
    const store = Searcher('transactions/realtime-histories')
    const head  = Header('transactions/realtime-histories', '이상거래 관리')
    const headers:Record<string, string> = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'appr_num': '승인 번호',
        'trans_id': '거래번호',
        'result_code': '응답코드',
        'request_type': '요청타입',
        'message': '응답메세지',
        'amount': '거래금액',
        'acct_num': '계좌번호',
        'acct_bank_name': '입금은행명',
        'acct_bank_code': '은행코드',
        'trans_seq_num': '요청번호',
        'created_at': '생성시간',
        'updated_at': '업데이트시간',
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const exporter = async (type: number) => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(type, r.data.content)
    }
    
    const printer = (type:number, datas: RealtimeHistory[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
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
