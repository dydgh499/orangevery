import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'

export const rest_types = [
    {id: 0, title: '직접등록'},
    {id: 1, title: '공공기관 휴일'},
    {id: 2, title: '기념일'},
]

export const useSearchStore = defineStore('HolidaySearchStore', () => {
    const store = Searcher('services/holidays')
    const head = Header('services/holidays', '공휴일 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'rest_dt': '공휴일 날짜',
        'rest_name': '공휴일 명칭',
        'rest_type': '공휴일 타입',
        'created_at': '발송시간',
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
