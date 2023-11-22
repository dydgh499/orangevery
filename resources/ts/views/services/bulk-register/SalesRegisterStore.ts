import { Header } from '@/views/headers';
export const useRegisterStore = defineStore('salesRegisterStore', () => {
    const head = Header('salesforces/bulk-register', '영업점 대량등록 포멧')

    const headers: Record<string, string> = {
        user_name: '아이디(O)',
        user_pw: '패스워드(O)', 
        sales_name: '영업점 상호(0)',
        level: '등급(O)', 
        nick_name: '대표자명(O)',
        addr: '주소(X)', 
        phone_num: '휴대폰번호(X)', 
        resident_num: '주민등록번호(O)', 
        business_num: '사업자등록번호(O)', 
        sector: '업종(O)',
        acct_num: '계좌번호(O)',
        acct_name: '예금주(O)', 
        acct_bank_name: '입금은행명(O)',
        settle_tax_type: '정산세율(O)', 
        settle_cycle: '정산주기(O)', 
        settle_day: '정산일(O)',
        view_type: '화면 타입(O)',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    return {
        head, headers
    }
})
