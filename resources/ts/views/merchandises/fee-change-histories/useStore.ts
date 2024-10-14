import { getUserLevel } from '@/plugins/axios'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
export const useSearchStore = defineStore('mchtFeeHistorySearchStore', () => {
    const store = Searcher('merchandises/fee-change-histories')
    const head  = Header('merchandises/pay-modules', '결제모듈 관리')
    
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'created_at' : '생성시간',
        'apply_dt': '적용예정일',
        'change_status' : '변경상태',
        'bf_trx_fee' : '수수료',
        'bf_hold_fee' : '유보금 수수료',
        'aft_trx_fee' : '수수료',
        'aft_hold_fee' : '유보금 수수료',
    }

    head.sub_headers.value = [
        head.getSubHeaderFormat('적용정보', 'id', 'change_status', 'string', 5),
        head.getSubHeaderFormat('이전 값', 'bf_trx_fee', 'bf_hold_fee', 'string', 2),
        head.getSubHeaderFormat('변경 값', 'aft_trx_fee', 'aft_hold_fee', 'string', 2),
    ]
    if(getUserLevel() >= 35) {
        headers['remove'] = '삭제'
        head.sub_headers.value.push(head.getSubHeaderFormat('', 'remove', 'remove', 'string', 1))
    }

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
    return {
        store,
        head,
        exporter,
    }
})
