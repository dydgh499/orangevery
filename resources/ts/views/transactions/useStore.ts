import { Searcher } from '@/views/searcher';
import type { Transaction } from '@/views/types';
import corp from '@corp';

export const useSearchStore = defineStore('transSearchStore', () => {    
    const store = Searcher<Transaction>('transactions', <Transaction>({}))
    const levels = corp.pv_options.auth.levels

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('거래 타입', 'module_type')
        if(levels.dev_use)
        {
            store.setHeader(levels.dev_name+' ID', 'dev_name')
            store.setHeader('수수료 ', 'dev_fee')    
        }        
        if(levels.sales5_use)
        {
            store.setHeader(levels.sales5_name+' ID', 'sales5_name')
            store.setHeader('수수료 ', 'sales5_fee')    
        }
        if(levels.sales4_use)
        {
            store.setHeader(levels.sales4_name+' ID', 'sales4_name')
            store.setHeader('수수료  ', 'sales4_fee')    
        }
        if(levels.sales3_use)
        {
            store.setHeader(levels.sales3_name+' ID', 'sales3_name')
            store.setHeader('수수료   ', 'sales3_fee')    
        }
        if(levels.sales2_use)
        {
            store.setHeader(levels.sales2_name+' ID', 'sales2_name')
            store.setHeader('수수료    ', 'sales2_fee')    
        }
        if(levels.sales1_use)
        {
            store.setHeader(levels.sales1_name+' ID', 'sales1_name')
            store.setHeader('수수료     ', 'sales1_fee')    
        }
        if(levels.sales0_use)
        {
            store.setHeader(levels.sales0_name+' ID', 'sales0_name')
            store.setHeader('수수료      ', 'sales0_fee')    
        }
        store.setHeader('가맹점 ID', 'user_name')
        store.setHeader('수수료        ', 'mcht_fee')
        store.setHeader('가맹점 상호', 'mcht_name')
        store.setHeader('유보금 수수료', 'hold_fee')
        store.setHeader('정산일', 'pay_cond_id')
        store.setHeader('입금 수수료', 'pay_cond_price')
        store.setHeader('PG사', 'pg_id')
        store.setHeader('구간 ', 'ps_id')
        store.setHeader('구간 수수료', 'ps_fee')
        store.setHeader('커스텀필터', 'custom_id')
        store.setHeader('단말기타입', 'terminal_id')
        store.setHeader('거래 금액', 'amount')
        store.setHeader('거래 수수료', 'trx_amount')
        store.setHeader('정산 금액', 'profit')
        store.setHeader('거래 시간', 'trx_dttm')
        store.setHeader('취소 시간', 'cxl_dttm')
        store.setHeader('할부', 'installment')
        store.setHeader('MID', 'mid')
        store.setHeader('TID', 'tid')
        store.setHeader('발급사', 'issuer')
        store.setHeader('발행사', 'acquirer')
        store.setHeader('카드번호', 'card_num')
        store.setHeader('카드명', 'card_name')
        store.setHeader('구매자명', 'buyer_name')
        store.setHeader('구매자 연락처', 'buyer_phone')
        store.setHeader('상품명', 'item_name')
        store.setHeader('주문번호', 'ord_num')
        store.setHeader('거래번호', 'trx_id')
        store.setHeader('원거래번호', 'ori_trx_id')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('더보기', 'extra_col')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})
