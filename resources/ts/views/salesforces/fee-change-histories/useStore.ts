import { Searcher } from '@/views/searcher';
import type { SalesFeeChangeHistory } from '@/views/types';

export const useSearchStore = defineStore('salesFeeHistorySearchStore', () => {
    const store = Searcher<SalesFeeChangeHistory>('salesforces/fee-change-histories', <SalesFeeChangeHistory>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점명', 'mcht_name')
        store.setHeader('등급', 'level')
        store.setHeader('이전 영업점', 'bf_sales_name')
        store.setHeader('변경 영업점', 'aft_sales_name')
        store.setHeader('이전 수수료', 'bf_trx_fee')
        store.setHeader('변경 수수료', 'aft_trx_fee')
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
