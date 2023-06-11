import { Searcher } from '@/views/searcher';
import type { SettleMerchandise } from '@/views/types';

export const useSearchStore = defineStore('transSettlesMchtSearchStore', () => {    
    const store = Searcher<SettleMerchandise>('transactions/settle/merchandises', <SettleMerchandise>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점 ID', 'user_name')
        store.setHeader('상호', 'mcht_name')
        store.setHeader('입금 수수료', 'pay_cond_price')
        store.setHeader('유보금 수수료', 'hold_fee')
        
        store.setHeader('매출건수', 'appr.count')
        store.setHeader('총승인액', 'appr.amount')
        store.setHeader('수수료', 'appr.trx_fee')
        store.setHeader('입금 수수료', 'appr.dpst_fee')
        store.setHeader('보유금액 수수료', 'appr.hold_fee')
        
        store.setHeader('취소건수', 'cxl.count')
        store.setHeader('총취소액', 'cxl.amount')
        store.setHeader('수수료', 'cxl.trx_fee')
        store.setHeader('입금 수수료', 'cxl.dpst_fee')
        store.setHeader('보유금액 수수료', 'cxl.hold_fee')
        
        store.setHeader('총매출액', 'sales_amount')
        store.setHeader('총정산액', 'total_profit')
        
        store.setHeader('추가차감금', 'deduction.amount')
        store.setHeader('추가차감', 'deduction.amount_input')
        store.setHeader('차감완료금', 'deduction.complate_amount')
                
        store.setHeader('통신비', 'comm_amount')
        store.setHeader('정산금액', 'settle_amount')
        store.setHeader('입금금액', 'deposit_amount')
        store.setHeader('이체금액', 'transfer_amount')
        
        store.setHeader('은행', 'acct_bank_nm')
        store.setHeader('은행코드', 'acct_bank_cd')
        store.setHeader('예금주', 'acct_nm')
        store.setHeader('계좌번호', 'acct_num')
        store.setHeader('대표자명', 'nick_name')
        store.setHeader('연락처', 'phone_num')
        store.setHeader('사업자등록번호', 'resident_num')
        store.setHeader('주민등록번호', 'business_num')
        store.setHeader('업종', 'sector')
        store.setHeader('주소', 'addr')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})
