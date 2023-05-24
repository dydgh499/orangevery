import { Searcher } from '@/views/searcher';
import type { Complaint } from '@/views/types';

export const useSearchStore = defineStore('complaintSearchStore', () => {
    const store = Searcher<Complaint>('complaints', <Complaint>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('TID', 'tid')
        store.setHeader('고객명', 'cust_nm')
        store.setHeader('승인일', 'appr_dt')
        store.setHeader('승인번호', 'appr_num')
        store.setHeader('연락처', 'phone_num')
        store.setHeader('수기작성성함', 'hand_cust_nm')
        store.setHeader('수기작성연락처', 'hand_phone_num')
        store.setHeader('발급사', 'issuer_name')
        store.setHeader('PG사', 'pg_name')
        store.setHeader('민원타입', 'type')
        store.setHeader('인입경로', 'entry_path')
        store.setHeader('입금상태', 'dpst_status')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
    }
})

export const useUpdateStore = defineStore('complaintUpdateStore', () => {
    const path  = 'complaints'
    const item  = reactive<Complaint>({
        id: 0,
        brand_id: 0,
        tid: '',
        cust_nm: '',
        appr_dt: null,
        appr_num: '',
        phone_num: '',
        hand_cust_nm: '',
        hand_phone_num: '',
        issuer_id: '',
        type:0,
        pg_id: '',
        entry_path: '',
        is_deposit: '',
        note: ''
    })
    return {
        path, item
    }
})
