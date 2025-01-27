import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { SubBusinessRegistration } from '@/views/types'

export const registration_types = [
    {id:0, title:'신규'},
    {id:1, title:'해지'},
    {id:2, title:'변경'},
]

export const useSearchStore = defineStore('subBusinessRegistrationSearchStore', () => {
    const store     = Searcher('merchandises/sub-business-registrations')
    const head      = Header('merchandises/sub-business-registrations', '하위사업자등록 결과')

    const headers: Record<string, string> = {
        'id': 'NO.',
        'registration_code': '등록상태',
        'registration_msg' : '결과 메세지',
        'registration_type': '등록타입',
        'card_company_name': '카드사명',
        'pg_type': '원천사',
        'mcht_name': '가맹점 상호',
        'business_num': '사업자번호',
        'registration_dt'  : '가입일',
        'req_dt'           : '요청일',
        'updated_at' : '업데이트시간'
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
})

export const defaultItemInfo = () => {
    const path = 'merchandises/sub-business-registrations'
    const item = reactive<SubBusinessRegistration>({
        id: 0,
        mcht_id: 0,
        pg_type: 0,
        registration_msg: 0,
        card_company_name: 0
    })
    return {
        path, item
    }
}
