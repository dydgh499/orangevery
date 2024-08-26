import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { SubBusinessRegistration } from '@/views/types'

export const registration_types = [
    {id:0, title:'신규'},
    {id:1, title:'해지'},
    {id:2, title:'변경'},
]

export const registrationResults = (registration_result: number) => {
    if(registration_result === -1)
        return '신청대기'
    else if(registration_result === -5)
        return '신청중'
    else if(registration_result === 0)
        return '완료'
    else
        return '실패'
}

export const registrationResultColor = (registration_result: number) => {
    if(registration_result === -1)
        return "default"    
    else if(registration_result === -5)
            return "primary"
    else if(registration_result === 0)
        return "success"
    else
        return "error"
}

export const useSearchStore = defineStore('subBusinessRegistrationSearchStore', () => {
    const store     = Searcher('merchandises/sub-business-registrations')
    const head      = Header('merchandises/sub-business-registrations', '하위사업자등록 결과')

    const headers: Record<string, string> = {
        'id': 'NO.',
        'business_num': '사업자번호',
        'pg_type': 'PG사',
        'card_company_name': '카드사명',
        'registration_type': '등록타입',
        'registration_result': '등록상태',        
        'registration_msg': '결과 메세지',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간'
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
