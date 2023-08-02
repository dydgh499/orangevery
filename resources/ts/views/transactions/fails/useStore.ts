import { Header } from '@/views/headers'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { FailTransaction } from '@/views/types'


export const useSearchStore = defineStore('failSearchStore', () => {    
    const store = Searcher('transactions/fails')
    const head  = Header('transactions/fails', '결제실패 관리')
    const { pgs, pss } = useStore()
    const headers = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'module_type': '거래타입',
        'amount': '결제시도 금액',
        'result_cd': '실패 코드',
        'result_msg': '실패 메세지',
        'pg_id' : 'PG사',
        'ps_id' : '구간',
        'trx_dttm': '결제시도시간',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    
    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        printer(type, r.data.content)
    }
    
    const printer = (type:number, datas: FailTransaction[]) => {
        const keys = Object.keys(headers);
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
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
