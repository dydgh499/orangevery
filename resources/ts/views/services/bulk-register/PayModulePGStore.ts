import { Header } from '@/views/headers';
export const useRegisterStore = defineStore('PayModulePGStore', () => {
    const head = Header('pay-modules/pg-bulk-updater', '가맹점 구간 일괄변경 포멧')

    const headers: Record<string, string> = {
        mcht_name: '가맹점 상호(O)',
        pg_id: 'PG사명(O)',
        ps_id: '구간(O)',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    return {
        head, headers
    }
})
