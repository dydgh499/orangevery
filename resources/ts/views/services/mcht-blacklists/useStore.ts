import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Options } from '@/views/types';

export const block_types = <Options[]>([
    {id:0, title:'상호'},
    {id:1, title:'대표자명'},
    {id:2, title:'사업자번호'},
    {id:3, title:'휴대폰번호'},
    {id:4, title:'주민번호'},
    {id:5, title:'주소'},
])

export const useSearchStore = defineStore('mchtBlacklistSearchStore', () => {
    const store = Searcher('services/mcht-blacklists')
    const head = Header('services/mcht-blacklists', '가맹점 블랙리스트 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'block_type': '차단 타입',
        'block_content': '차단 값',
        'block_reason': '차단 내용',
        'created_at': '생성 시간',
        'updated_at': '업데이트 시간',
        'extra_col': '더보기',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)            
            datas[i]['block_type'] = block_types.find(obj => obj.id === datas[i]['block_type'])?.title
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    
    onMounted(async () => {})
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})
