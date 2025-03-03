import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import { transactionHeader } from '@/views/transactions/transacitonsHeader';

export const useSearchStore = defineStore('notiSendHistorySearchStore', () => {
    const store = Searcher('merchandises/noti-send-histories')
    const head  = Header('merchandises/noti-send-histories', '노티 발송이력 관리')
    const table = transactionHeader('transactions')
    delete table.headers0['id']
    delete table.headers0['module_type']
    delete table.headers0['note']
    const getSendResponseHeader = () => {
        return {
            'id': 'NO.',
            'trans_id': '거래 고유번호',
            'http_code': '응답코드',
            'message': '응답내용',
            'retry_count': '재시도 회수',    
        }
    }

    const getMerchandiseHeader = () => {
        return {
            'mcht_name': '가맹점 상호',
            'pmod_note': '결제모듈 별칭',
            'module_type': '모듈타입',    
        }
    }

    const getEtcCols = () => {
        return {
            'extra_col': '더보기'
        }
    }
    
    const headers: Record<string, string> = {
        ...getSendResponseHeader(),
        ...getMerchandiseHeader(),
        ...table.headers0,
        ...table.headers5,
        ...getEtcCols(),
    }
    const sub_headers: any = []
    head.getSubHeaderCol('노티결과', getSendResponseHeader(), sub_headers)
    head.getSubHeaderCol('가맹점 정보', getMerchandiseHeader(), sub_headers)
    head.getSubHeaderCol('거래 정보', table.headers0, sub_headers)
    head.getSubHeaderCol('결제 정보', table.headers5, sub_headers)
    head.getSubHeaderCol('기타 정보', getEtcCols(), sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }

    return {
        store,
        head,
        exporter,
    }
});
