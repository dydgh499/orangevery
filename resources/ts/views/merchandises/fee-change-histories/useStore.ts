import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
export const useSearchStore = defineStore('mchtFeeHistorySearchStore', () => {
    const store = Searcher('merchandises/fee-change-histories')
    const head  = Header('merchandises/pay-modules', '결제모듈 관리')
    
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'apply_dt': '적용일',
        'mcht_name' : '가맹점 상호',
        'bf_trx_fee' : '이전 수수료',
        'aft_trx_fee' : '변경 수수료',
        'bf_hold_fee' : '이전 유보금 수수료',
        'aft_hold_fee' : '변경 유보금 수수료',
        'change_status' : '변경상태',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
        'extra_col': '더보기',
    }

    head.sub_headers.value = []
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
