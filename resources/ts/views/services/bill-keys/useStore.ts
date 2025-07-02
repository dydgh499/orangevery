import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('billKeySearchStore', () => {    
    const store = Searcher('services/bill-keys')
    const head  = Header('services/bill-keys', '빌키 관리')
    const headers_2 = {
        'buyer_name': '구매자명',
        'buyer_phone': '구매자 번호',
        'issuer': '발급사',
        'card_num': '카드번호',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
        'extra_col': '더보기',
    }
    const headers: Record<string, string> = {
        ...headers_2,
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])

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
        metas,
    }
});
