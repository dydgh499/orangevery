import { Searcher } from '@/views/searcher';
import type { NotiSendHistory } from '@/views/types';

export const useSearchStore = defineStore('notiSendHistorySearchStore', () => {
    const store = Searcher<NotiSendHistory>('merchandises/noti-send-histories', <NotiSendHistory>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('거래 고유번호', 'tid')
        store.setHeader('응답코드', 'http_code')
        store.setHeader('재시도 회수', 'retry_count')
        store.setHeader('내용', 'message')
        store.setHeader('발송 URL', 'send_url')
        store.setHeader('발송시간', 'created_at')
    }
    return {
        store,
        setHeaders,
    }
})
