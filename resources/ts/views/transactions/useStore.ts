import { Searcher } from '@/views/searcher';
import type { Transaction } from '@/views/types';

export const useSearchStore = defineStore('transSearchStore', () => {    
    const store = Searcher<Transaction>('transactions', <Transaction>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('거래 타입', 'module_type')
        store.setHeader('개발사 ID', 'dev_name')
        store.setHeader('수수료 ', 'dev_fee')
        store.setHeader('지사 ID', 'sales5_name')
        store.setHeader('수수료  ', 'sales5_fee')
        store.setHeader('하위지사 ID', 'sales4_name')
        store.setHeader('수수료   ', 'sales4_fee')
        store.setHeader('총판 ID', 'sales3_name')
        store.setHeader('수수료    ', 'sales3_fee')
        store.setHeader('하위총판 ID', 'sales2_name')
        store.setHeader('수수료     ', 'sales2_fee')
        store.setHeader('대리점 ID', 'sales1_name')
        store.setHeader('수수료      ', 'sales1_fee')
        store.setHeader('하위대리점 ID', 'sales0_name')
        store.setHeader('수수료       ', 'sales0_fee')
        store.setHeader('가맹점 ID', 'user_name')
        store.setHeader('수수료        ', 'trx_fee')
        store.setHeader('가맹점 상호', 'mcht_name')
        store.setHeader('유보금 수수료', 'hold_fee')
        store.setHeader('결제조건 수수료', 'pay_cond_fee')
        store.setHeader('PG사', 'pg_id')
        store.setHeader('구간', 'ps_id')
        store.setHeader('구간 수수료', 'ps_fee')

        store.setHeader('커스텀필터', 'custom_id')
        store.setHeader('수수료         ', 'custom_fee')

        store.setHeader('단말기타입', 'terminal_id')
        store.setHeader('수수료          ', 'terminal_fee')

        store.setHeader('거래시간', 'trx_dttm')
        store.setHeader('취소시간', 'cxl_dttm')
        store.setHeader('취소여부', 'is_cancel')
        store.setHeader('거래 타입', 'trx_type')
        store.setHeader('거래 금액', 'amount')
        store.setHeader('취소 금액', 'cxl_amount')
        store.setHeader('정산 금액', 'profit')
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
        store.setHeader('생성시간', 'created_at')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})
