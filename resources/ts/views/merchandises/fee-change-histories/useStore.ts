import { Searcher } from '@/views/searcher';
import type { MchtFeeChangeHistory } from '@/views/types';

export const useSearchStore = defineStore('mchtFeeHistorySearchStore', () => {
    const store = Searcher<MchtFeeChangeHistory>('merchandises/fee-change-histories', <MchtFeeChangeHistory>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점명', 'mcht_name')
        store.setHeader('변경된 수수료', 'trx_fee')
        store.setHeader('변경된 유보금 수수료', 'hold_fee')
        store.setHeader('상위 영업자명', 'sales_name')
        store.setHeader('변경상태', 'change_status')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
        store.sortHeader()
    }
    return {
        store,
        setHeaders,
    }
})
