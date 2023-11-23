import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Complaint, Options } from '@/views/types'

export const complaint_types = <Options[]>[
    { id: 1, title: '유사수신' }, { id: 2, title: '유사투자' },
    { id: 3, title: '단순소명' }, { id: 4, title: '기타' },
]
export const complaint_statuses = <Options[]>[
    { id: 0, title: '처리전' }, { id: 1, title: '처리중' },
    { id: 2, title: '처리완료' },
]
export const issuers = [
    '비씨', '국민', '하나', '삼성',
    '신한', '현대', '롯데', '시티',
    '농협', '수협', '우리', '광주',
    '전북', '제주', '해외비자', '해외마스터',
    '해외다이너스', '해외AMAX', '해외JCB', '해외',
    '우체국', 'MG새마을체크', '중국은행체크', '은련',
    '신협', '저축은행', 'KDB산업', '카카오뱅크',
    '케이뱅크', '카카오머니', '강원', 'UNIONPAY',
    '(구)미래에셋증권', '한국투자증권',  '카카오페이증권'
]

export const useSearchStore = defineStore('complaintSearchStore', () => {
    const store = Searcher('complaints')
    const head  = Header('services/brands', '서비스 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'tid' : 'TID',
        'cust_name' : '고객명',
        'appr_dt' : '승인일',
        'appr_num' : '승인번호',
        'phone_num' : '연락처',
        'hand_cust_name' : '수기작성성함',
        'hand_phone_num' : '수기작성연락처',
        'note'  : '민원내용',
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
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {      
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
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
