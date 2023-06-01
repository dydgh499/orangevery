import { Searcher } from '@/views/searcher';
import type { SalesFeeChangeHistory } from '@/views/types';

export const useSearchStore = defineStore('salesFeeHistorySearchStore', () => {
    const store = Searcher<SalesFeeChangeHistory>('salesforces/fee-change-histories', <SalesFeeChangeHistory>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('영업점명', 'sales_name')
        store.setHeader('변경된 수수료', 'trx_fee')
        store.setHeader('변경된 정산세율 타입', 'hold_fee')
        store.setHeader('변경상태', 'change_status')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
    }
})
