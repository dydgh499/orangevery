import { getUserLevel } from '@/plugins/axios';
import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('billKeySearchStore', () => {    
    const store = Searcher('merchandises/bill-keys')
    const head  = Header('merchandises/bill-keys', '빌키 관리')

    const headers_0: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
    }
    const headers_1: Record<string, string> = {}

    if(getUserLevel() >= 35)
        headers_1['pg_id'] = '원천사'

    const headers_2 = {
        'buyer_name': '구매자명',
        'buyer_phone': '구매자 번호',
        'issuer': '발급사',
        'card_num': '카드번호',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    const headers: Record<string, string> = {
        ...headers_0,
        ...headers_1,
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
