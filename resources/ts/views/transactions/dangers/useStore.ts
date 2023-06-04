import { Searcher } from '@/views/searcher';
import type { Danger } from '@/views/types';

export const useSearchStore = defineStore('dangerSearchStore', () => {    
    const store = Searcher<Danger>('transactions/dangers', <Danger>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점 상호', 'mcht_name')
        store.setHeader('거래 타입', 'trx_type')
        store.setHeader('거래금액', 'amount')
        store.setHeader('상품명', 'item_nm')
        store.setHeader('주문번호', 'ord_num')
        store.setHeader('거래번호', 'trx_id')
        store.setHeader('원거래번호', 'ori_trx_id')
        store.setHeader('MID', 'mid')
        store.setHeader('TID', 'cat_id')
        store.setHeader('거래수수료', 'withdraw_fee')
        store.setHeader('발급사', 'issuer')
        store.setHeader('매입사', 'acquirer')
        store.setHeader('카드번호', 'card_num')
        store.setHeader('승인번호', 'appr_num')
        store.setHeader('할부', 'installment')
        store.setHeader('거래시간', 'trx_dttm')
        store.setHeader('구매자명', 'buyer_nm')
        store.setHeader('구매자 연락처', 'buyer_phone')
        store.setHeader('이상거래 타입', 'danger_type')
        store.setHeader('확인 여부', 'danger_check')
        store.setHeader('생성시간', 'created_at')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})
