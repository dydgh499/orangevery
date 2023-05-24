import { Searcher } from '@/views/searcher';
import type { SettlesHistoriesSalesforce } from '@/views/types';

export const useSearchStore = defineStore('transSettlesHistoryMchtSearchStore', () => {    
    const store = Searcher<SettlesHistoriesSalesforce>('transactions/settles-history/salesforces', <SettlesHistoriesSalesforce>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('영업점 명', 'mcht_name')
        store.setHeader('정산금액', 'settle_amount')

        store.setHeader('정산일', 'settle_dt')
        store.setHeader('입금일', 'deposit_dt')
        store.setHeader('정산 적용 시작일', 'apply_s_dt')
        store.setHeader('정산 적용 종료일', 'apply_e_dt')

        store.setHeader('은행', 'acct_bank_nm')
        store.setHeader('은행코드', 'acct_bank_cd')
        store.setHeader('계좌번호', 'acct_num')
        store.setHeader('예금주', 'acct_nm')
        store.setHeader('상태', 'status')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
    }
})
