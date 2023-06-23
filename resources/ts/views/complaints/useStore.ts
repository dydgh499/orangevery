import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Complaint, Options } from '@/views/types';

export const issuers = <Options[]>[
    { id: 1, title: '비씨' }, { id: 2, title: '국민' }, { id: 3, title: '하나' },
    { id: 4, title: '삼성' }, { id: 6, title: '신한' }, { id: 7, title: '현대' },
    { id: 8, title: '롯데' }, { id: 11, title: '시티' }, { id: 12, title: '농협' },
    { id: 13, title: '수협' }, { id: 15, title: '우리' }, { id: 21, title: '광주' },
    { id: 22, title: '전북' }, { id: 23, title: '제주' }, { id: 25, title: '해외비자' },
    { id: 26, title: '해외마스터' }, { id: 27, title: '해외다이너스' }, { id: 28, title: '해외AMAX' },
    { id: 29, title: '해외JCB' }, { id: 30, title: '해외' }, { id: 32, title: '우체국' }, { id: 33, title: 'MG새마을체크' },
    { id: 34, title: '중국은행체크' }, { id: 38, title: '은련' }, { id: 41, title: '신협' },
    { id: 42, title: '저축은행' }, { id: 43, title: 'KDB산업' }, { id: 44, title: '카카오뱅크' },
    { id: 45, title: '케이뱅크' }, { id: 46, title: '카카오머니' }, { id: 47, title: '강원' },
    { id: 48, title: 'UNIONPAY' }, { id: 238, title: '(구)미래에셋증권' }, { id: 243, title: '한국투자증권' },
    { id: 288, title: '카카오페이증권' },
]

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
        'issuer_id' : '발급사',
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
        const r = await store.get(store.getAllDataFormat())
        const datas = r.data.content;
        for (let i = 0; i <datas.length; i++) {
            datas[i]['type'] = complaint_types.find(types => types.id === datas[i]['type'])?.title
            datas[i]['issuer_id'] = issuers.find(issuer => issuer.id === datas[i]['issuer_id'])?.title
            datas[i]['is_deposit'] = datas[i]['is_deposit'] ? '입금' : '미입금'
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
        issuer_id: null,
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
