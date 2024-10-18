import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Complaint, Options } from '@/views/types'
import { getUserLevel } from '@axios'

export const complaint_types = <Options[]>[
    { id: 1, title: '본인미사용' }, { id: 2, title: '유사투자' },
    { id: 3, title: '거래내역' }, { id: 4, title: '취소요청' },
    { id: 5, title: '단순소명' }, { id: 6, title: '기타' },
]
export const complaint_statuses = <Options[]>[
    { id: 0, title: '처리전' }, { id: 1, title: '처리중' },
    { id: 2, title: '처리완료' },
]

export const issuers = [
    'BC', '비씨', '국민', '하나', '삼성',
    '신한', '현대', '롯데', '씨티', '농협', 
    '수협', '우리', '광주', '전북', '제주',
]

export const useSearchStore = defineStore('complaintSearchStore', () => {
    const store = Searcher('complaints')
    const head  = Header('complaints', '민원 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'created_at' : '접수시간',
        'cust_name' : '고객명',
        'appr_dt' : '승인일',
        'appr_num' : '승인번호',
        'phone_num' : '연락처',
        'note'  : '민원내용',
    };
    if(getUserLevel() >= 35)
        headers['pg_name'] = 'PG사'
    headers['type'] = '민원타입'
    headers['tid']  = '거래번호'

    if(getUserLevel() >= 35)
        headers['is_deposit'] = '입금상태'
    headers['entry_path'] = '인입경로'
    headers['updated_at'] = '업데이트시간'
    
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        const datas = r.data.content;
        for (let i = 0; i <datas.length; i++) {
            datas[i]['type'] = complaint_types.find(types => types.id === datas[i]['type'])?.title
            datas[i]['is_deposit'] = datas[i]['is_deposit'] ? '입금' : '미입금'
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)        
    }
    return {
        store,
        head,
        exporter,
    }
})

export const defaultItemInfo = () => {
    const path = 'complaints'
    const item = reactive<Complaint>({
        id: 0,
        mcht_id: null,
        tid: '',
        cust_name: '',
        appr_dt: null,
        appr_num: '',
        phone_num: '',
        hand_cust_name: '',
        hand_phone_num: '',
        issuer: null,
        type: null,
        pg_id: null,
        entry_path: '',
        is_deposit: 0,
        note: '',
        mcht_name: null,
        pg_name: null,
        complaint_status: 0
    })
    return {
        path, item
    }
}
