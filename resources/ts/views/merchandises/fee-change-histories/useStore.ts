import { Searcher } from '@/views/searcher';
import type { MchtFeeChangeHistory } from '@/views/types';

export const useSearchStore = defineStore('mchtFeeHistorySearchStore', () => {
    const store = Searcher<MchtFeeChangeHistory>('merchandises/fee-change-histories', <MchtFeeChangeHistory>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점명', 'mcht_name')
        store.setHeader('이전 수수료', 'bf_trx_fee')
        store.setHeader('변경 수수료', 'aft_trx_fee')
        store.setHeader('이전 유보금 수수료', 'bf_hold_fee')
        store.setHeader('변경 유보금 수수료', 'aft_hold_fee')
        store.setHeader('변경상태', 'change_status')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})
