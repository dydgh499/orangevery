import { Searcher } from '@/views/searcher';
import type { Transaction } from '@/views/types';

export const useSearchStore = defineStore('transSearchStore', () => {    
    const store = Searcher<Transaction>('transactions', <Transaction>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('영업점 수수료 합계', 'total_sf_fee')
        store.setHeader('가맹점 상호/수수료', 'mcht_name') //trx_fee
        store.setHeader('영업점 ID/수수료', 'sales_name')   //trx_fee
        store.setHeader('거래수수료', 'withdraw_fee')
        store.setHeader('유보금 수수료', 'hold_fee')
        store.setHeader('거래 타입', 'trx_type')
        store.setHeader('거래 금액', 'amount')
        store.setHeader('구매자명', 'buyer_nm')
        store.setHeader('구매자 연락처', 'buyer_phone')
        store.setHeader('상품명', 'item_nm')
        store.setHeader('주문번호', 'ord_num')
        store.setHeader('거래번호', 'trx_id')
        store.setHeader('원거래번호', 'ori_trx_id')
        store.setHeader('MID', 'mid')
        store.setHeader('TID', 'cat_id')
        store.setHeader('발급사', 'card_nm')
        store.setHeader('발행사', 'acquirer')
        store.setHeader('카드번호', 'card_num')
        store.setHeader('할부', 'installment')
        store.setHeader('이상거래', 'danger_type')
        store.setHeader('이상거래확인여부', 'danger_check')
        store.setHeader('PG사', 'pg_name')
        store.setHeader('구간', 'ps_name')
        store.setHeader('거래시간', 'trx_dttm')
        store.setHeader('취소시간', 'cxl_dttm')
        store.setHeader('취소여부', 'is_cancel')
        store.setHeader('생성시간', 'created_at')
    }
    return {
        store,
        setHeaders,
    }
})
