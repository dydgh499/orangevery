import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('billKeySearchStore', () => {    
    const store = Searcher('merchandises/pay-modules/bill-keys')
    const head  = Header('merchandises/pay-modules/bill-keys', '빌키 관리')

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'note' : '결제모듈 별칭',
        'buyer_name': '구매자명',
        'buyer_phone': '구매자 번호',
        'issuer': '발급사',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
        'extra_col': '더보기',
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
