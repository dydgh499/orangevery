import { Header } from '@/views/headers';
export const useRegisterStore = defineStore('regularCardRegisterStore', () => {
    const head = Header('regular-cards/bulk-register', '단골고객 카드정보 대량등록 포멧')

    const headers: Record<string, string> = {
        mcht_name: '가맹점 상호(O)',
        note: '별칭(O)',
        card_num: '카드번호(O)',
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    return {
        head, headers
    }
})
