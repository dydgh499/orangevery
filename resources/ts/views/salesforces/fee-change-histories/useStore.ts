import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('salesFeeHistorySearchStore', () => {
    const store = Searcher('salesforces/fee-change-histories')
    const head  = Header('salesforces/fee-change-histories', '영업점 수수료율 변경이력')

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'user_name' : '영업점 상호',
        'level': '등급',
        'note' : '별칭',
        'module_type' : '모듈타입',
        'pg_id' : 'PG사명',
        'ps_id' : '구간',
        'settle_type' : '정산일',
        'mid' : 'MID',
        'tid' : 'TID',
        'installment' : '할부한도',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
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
})
