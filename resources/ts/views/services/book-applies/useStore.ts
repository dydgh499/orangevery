import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';

export const useSearchStore = defineStore('bookApplySearchStore', () => {
    const store = Searcher('services/book-applies')
    const head = Header('services/book-applies', '예약변경 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'dest_name': '상호',
        'note': '별칭',
        'change_status': '변경상태',
        'apply_data': '변경 값',
        'created_at': '생성시간',
        'apply_at': '변경예정시간',
        'updated_at': '업데이트시간',
        'extra_col': '삭제하기',
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])
    const exporter = async () => {
        let keys = Object.keys(head.flat_headers.value)
        if(store.params.dest_type < 2)
            keys = keys.filter(val => val !== 'note');

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
