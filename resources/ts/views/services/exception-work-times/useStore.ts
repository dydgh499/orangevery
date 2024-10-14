import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'

export const useSearchStore = defineStore('ExceptionWorkTimeSearchStore', () => {
    const store = Searcher('services/exception-work-times')
    const head = Header('services/exception-work-times', '공휴일 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'user_name': '운영자 ID',
        'nick_name': '운영자명',
        'work_s_at': '작업시작 시간',
        'work_e_at': '작업종료 시간',
        'created_at': '생성시간',
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
            datas[i]['rest_type'] = store.getSelectIdColor(datas[i]['rest_type'])
        }
        head.exportToExcel(datas)
    }
        
    return {
        store,
        head,
        exporter,
        metas,
    }
})
