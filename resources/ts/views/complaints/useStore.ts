import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Complaint, Options } from '@/views/types'

export const complaint_types = <Options[]>[
    { id: 1, title: '유사수신' }, { id: 2, title: '유사투자' },
    { id: 3, title: '단순소명' }, { id: 4, title: '기타' },
]

export const useSearchStore = defineStore('complaintSearchStore', () => {
    const store = Searcher('complaints')
    const head  = Header('services/brands', '서비스 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'tid' : 'TID',
        'cust_nm' : '고객명',
        'appr_dt' : '승인일',
        'appr_num' : '승인번호',
        'phone_num' : '연락처',
        'hand_cust_nm' : '수기작성성함',
        'hand_phone_num' : '수기작성연락처',
        'issuer' : '발급사',
        'pg_name': 'PG사',
        'type': '민원타입',
        'is_deposit': '입금상태',
        'entry_path': '인입경로',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    head.main_headers.value = [
    ];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {      
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        const datas = r.data.content;
        for (let i = 0; i <datas.length; i++) {
            datas[i]['type'] = complaint_types.find(types => types.id === datas[i]['type'])?.title
            datas[i]['is_deposit'] = datas[i]['is_deposit'] ? '입금' : '미입금'
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }
    return {
        store,
        head,
        exporter,
    }
})

export const useUpdateStore = defineStore('complaintUpdateStore', () => {
    const path = 'complaints'
    const item = reactive<Complaint>({
        id: 0,
        mcht_id: null,
        tid: '',
        cust_nm: '',
        appr_dt: null,
        appr_num: '',
        phone_num: '',
        hand_cust_nm: '',
        hand_phone_num: '',
        issuer: null,
        type: null,
        pg_id: null,
        entry_path: '',
        is_deposit: false,
        note: '',
        mcht_name: null,
        pg_name: null
    })
    return {
        path, item
    }
})
