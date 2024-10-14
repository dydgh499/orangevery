import { Header } from '@/views/headers'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Danger } from '@/views/types'
import { Options } from '@/views/types'
import { getUserLevel } from '@axios'

export const danger_types = <Options[]>([
    {id:0, title:'중복결제'}, {id:1, title:'한도초과'}, {id:2, title:'할부초과'}
])

export const useSearchStore = defineStore('dangerSearchStore', () => {    
    const store = Searcher('transactions/dangers')
    const head  = Header('transactions/dangers', '이상거래 관리')
    const { pgs, pss, terminals } = useStore()
    const headers:Record<string, string> = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'module_type': '거래 타입',
        'item_name': '상품명',
        'amount': '거래금액',
        'ord_num': '주문번호',
        'appr_num': '승인번호',
        'installment': '할부',
        'mid': 'MID',
        'tid': 'TID',
        'pg_id': 'PG사',
        'ps_id': '구간',
        'terminal_id': '장비 타입',
        'issuer': '발급사',
        'acquirer': '매입사',
        'card_num': '카드번호',
        'trx_dttm': '거래시간',
        'buyer_name': '구매자명',
        'danger_type': '이상거래타입',
        'is_checked': '확인 여부',
    }
    if(getUserLevel() >= 35)
        headers['extra_col'] = '더보기'

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const exporter = async () => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(r.data.content)
    }
    
    const printer = (datas: Danger[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string
            datas[i]['is_checked'] = datas[i]['is_checked'] ? '확인' : '미확인'
            datas[i]['danger_type'] = danger_types.find(obj => obj.id === datas[i]['danger_type'])?.title
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
