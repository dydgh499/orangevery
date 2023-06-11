import { Searcher } from '@/views/searcher';
import type { Options, PayModule } from '@/views/types';

export const module_types = <Options[]>([
    { id: 0, title: "단말기" }, { id: 1, title: "수기결제" },
    { id: 2, title: "인증결제" }, { id: 3, title: "간편결제" },
])
export const installments = <Options[]>([
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },
])

export const useSearchStore = defineStore('payModSearchStore', () => {    
    const store = Searcher<PayModule>('merchandises/pay-modules', <PayModule>({}))
    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점 상호', 'mcht_name')
        store.setHeader('별칭', 'note')
        store.setHeader('모듈타입', 'module_type')
        store.setHeader('PG사명', 'pg_id')
        store.setHeader('구간', 'ps_id')
        store.setHeader('정산일', 'pay_cond_id')
        store.setHeader('MID', 'mid')
        store.setHeader('TID', 'tid')
        store.setHeader('할부한도', 'installment')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')    
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
});

export const useUpdateStore = defineStore('payModUpdateStore', () => {   
    const path  = 'merchandises/pay-modules'
    const item  = reactive<PayModule>({
        id: 0,
        mcht_id: 0,
        pg_id: 0,
        ps_id: 0,
        terminal_id: 0,
        pay_cond_id: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        comm_pr: 0,
        comm_calc_day: 1,
        comm_calc_level: 10,
        under_sales_amt: 0,
        begin_dt: undefined,
        ship_out_dt: undefined,
        ship_out_stat: false,
        is_old_auth: false,
        use_saleslip_prov: false,
        use_saleslip_sell: false,
        installment: 0,
        note: '비고'
    })
    //카드사 필터 및 다른 필터옵션들
    return {
        path, item
    }
});
