import { Header } from '@/views/headers';
export const useRegisterStore = defineStore('MchtBlacklistRegisterStore', () => {
    const head = Header('mcht-blacklists/bulk-register', '가맹점 블랙리스트 대량등록 포멧')

    const headers: Record<string, string> = {
        mcht_name: '가맹점 상호',
        nick_name: '대표자명(X)',
        phone_num: '전화번호(X)',
        business_num: '사업자번호(X)',
        resident_num: '주민등록번호(X)',
        addr: '주소(X)',
        block_reason: '차단 사유(O)',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    return {
        head, headers
    }
})
