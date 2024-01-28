import { Header } from '@/views/headers';

export const useRegisterStore = defineStore('NotiUrlRegisterStore', () => {
    const head = Header('noti-urls/bulk-register', '노티주소 대량등록 포멧')

    const headers: Record<string, string> = {
        mcht_name: '가맹점 상호(O)',
        noti_status: '노티 사용 유무(O)',
        note: '별칭(O)',
        send_url: '발송 URL(O)',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    return {
        head, headers
    }
})
