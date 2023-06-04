import { Searcher } from '@/views/searcher';
import type { FailTransaction } from '@/views/types';

export const useSearchStore = defineStore('failSearchStore', () => {    
    const store = Searcher<FailTransaction>('transactions/fails', <FailTransaction>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점 상호', 'mcht_name')
        store.setHeader('실패 코드', 'result_cd')
        store.setHeader('실패 메세지', 'result_msg')
        store.setHeader('결제시도 금액', 'amount')
        store.setHeader('PG사', 'trx_type')
        store.setHeader('구간', 'amount')
        store.setHeader('결제모듈 별칭', 'pmod_name')
        store.setHeader('결제시도시간', 'trx_dttm')
        store.setHeader('생성시간', 'created_at')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})
