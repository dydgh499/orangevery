import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('failSearchStore', () => {    
    const store = Searcher('transactions/fails')
    const head  = Header('transactions/fails', '결제실패 관리')

    function setHeaders() {
        const headers = {
            'id': 'NO.',
            'mcht_name': '가맹점 상호',
            'result_cd': '실패 코드',
            'result_msg': '실패 메세지',
            'amount': '결제시도 금액',
            'trx_type': 'PG사', // ??
            'pmod_name': '결제모듈 별칭',
            'trx_dttm': '결제시도시간',
            'created_at': '생성시간',
        }
        head.main_headers.value = [
        ];
        head.headers.value = head.initHeader(headers, {})
        head.flat_headers.value = head.setFlattenHeaders()
    }
    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        let convert = r.data.content;
        for (let index = 0; index <convert.length; index++) 
        {
        
        }
        type == 1 ? head.exportToExcel(convert) : head.exportToPdf(convert)        
    }
    setHeaders()
    return {
        store,
        head,
        exporter,
    }
})
