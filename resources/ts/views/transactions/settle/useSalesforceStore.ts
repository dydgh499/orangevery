import { Searcher } from '@/views/searcher';
import type { SettleSalesforce } from '@/views/types';

export const useSearchStore = defineStore('transSettleSalesSearchStore', () => {    
    const store = Searcher<SettleSalesforce>('transactions/settle/salesforces', <SettleSalesforce>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('영업점명', 'mcht_name')
        store.setHeader('상위 영업점 ID', 'sales_name')
        store.setHeader('매출액', 'sales_amount')
        store.setHeader('정산액', 'total_profit')
        store.setHeader('개수', 'appr_count')
        store.setHeader('금액', 'appr_amount')
        store.setHeader('거래 수수료', 'appr_trx_fee')
        store.setHeader('입금 수수료', 'appr_dpst_fee')
        store.setHeader('유보금 수수료', 'appr_holding_fee')

        store.setHeader('개수', 'cxl_count')
        store.setHeader('금액', 'cxl_amount')
        store.setHeader('거래 수수료', 'cxl_trx_fee')
        store.setHeader('입금 수수료', 'cxl_dpst_fee')
        store.setHeader('유보금 수수료', 'cxl_holding_fee')

        store.setHeader('차감완료', 'deduction_amount')
        store.setHeader('추가차감', 'deduction_extra_amount')
        store.setHeader('차감완료금', 'deduction_complate_amount')

        store.setHeader('통신비', 'comm_amount')
        store.setHeader('정산금액', 'settle_amount')
        store.setHeader('입금금액', 'deposit_amount')
        store.setHeader('이체금액', 'transfer_amount')

        store.setHeader('은행', 'acct_bank_nm')
        store.setHeader('은행코드', 'acct_bank_cd')
        store.setHeader('계좌번호', 'acct_num')
        store.setHeader('예금주', 'acct_nm')
        store.setHeader('연락처', 'phone_num')
        store.setHeader('사업자등록번호', 'resident_num')
        store.setHeader('주민등록번호', 'business_num')
        store.setHeader('업종', 'sector')
        store.setHeader('주소', 'addr')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
    }
})
