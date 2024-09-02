import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('bookApplySearchStore', () => {
    const store = Searcher('services/book-applies')
    const head = Header('services/book-applies', '예약변경 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'dest_name': '상호',
        'pmod_note': '결제모듈 별칭',
        'change_status': '변경상태',
        'apply_at': '변경시간',
        'apply_data': '변경 값',
        'created_at': '생성시간',
        'extra_col': '삭제하기',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])
    const exporter = async (type: number) => {
        let keys = Object.keys(head.flat_headers.value)
        if(store.params.dest_type < 2)
            keys = keys.filter(val => val !== 'pmod_note');

        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    
    onMounted(async () => {

    })
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})
