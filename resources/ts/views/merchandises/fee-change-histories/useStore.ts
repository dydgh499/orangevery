import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
export const useSearchStore = defineStore('mchtFeeHistorySearchStore', () => {
    const store = Searcher('merchandises/fee-change-histories')
    const head  = Header('merchandises/pay-modules', '결제모듈 관리')
    
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'note' : '이전 수수료',
        'module_type' : '변경 수수료',
        'pg_id' : '이전 유보금 수수료',
        'ps_id' : '변경 유보금 수수료',
        'change_status' : '변경상태',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    
    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        let convert = r.data.content;
        for (let index = 0; index <convert.length; index++) {
        
        }
        type == 1 ? head.exportToExcel(convert) : head.exportToPdf(convert)        
    }
    return {
        store,
        head,
        exporter,
    }
})
