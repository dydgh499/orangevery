import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('notiSendHistorySearchStore', () => {
    const store = Searcher('merchandises/noti-send-histories')
    const head  = Header('merchandises/noti-send-histories', '노티 발송이력 관리')

        const headers: Record<string, string> = {
            'id': 'NO.',
            'trans_id': '거래 고유번호',
            'send_url': '발송 URL',
            'http_code': '응답코드',
            'message': '내용',
            'retry_count': '재시도 회수',
            'created_at': '발송시간',            
            'extra_col': '더보기',
        }
        head.main_headers.value = [];
        head.headers.value = head.initHeader(headers, {})
        head.flat_headers.value = head.setFlattenHeaders()
    

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }

    return {
        store,
        head,
        exporter,
    }
});
