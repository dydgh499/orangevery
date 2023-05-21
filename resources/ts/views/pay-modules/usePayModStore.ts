import { Searcher } from '@/views/searcher';
import type { PayModule } from '@/views/types';

export const useSearchStore = defineStore('payModSearchStore', () => {    
    const store = Searcher<PayModule>('pay-modules', <PayModule>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('PG사명', 'pg_name')
        store.setHeader('구간', 'ps_id')
        store.setHeader('출금타입', 'withdraw_name')
        store.setHeader('모듈타입', 'module_type')
        store.setHeader('단말기종류', 'terminal_id')        
        store.setHeader('MID', 'mid')
        store.setHeader('TID', 'tid')
        store.setHeader('시리얼 번호', 'serial_num')
        store.setHeader('통신비', 'addr')
        store.setHeader('정산일', 'acct_bank_nm')
        store.setHeader('정산주체', 'acct_bank_cd')
        store.setHeader('매출미달 차감금', 'acct_nm')
        store.setHeader('개통일', 'acct_num')
        store.setHeader('출고일', 'ship_out_dt')
        store.setHeader('출고상태', 'ship_out_stat')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')    
    }
    
    const pagenation = computed(() => {
        const firstIndex = store.items.value.length ? ((store.params.page - 1) * store.params.page_size) + 1 : 0
        const lastIndex = store.items.value.length + ((store.params.page - 1) * store.params.page_size)
        return `총 ${store.pagenation.total_count}개 항목 중 ${firstIndex} ~ ${lastIndex}개 표시`
      })
    return {
        store,
        pagenation,
        setHeaders,
    }
});

export const useUpdateStore = defineStore('payModUpdateStore', () => {   
    const path  = 'pay-modules'
    const item  = reactive<PayModule>({
        id: 0,
        brand_id: 0,
        mcht_id: 0,
        pg_id: 0,
        ps_id: 0,
        withdraw_id: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        ternimal_id: 0,
        widthdraw_id: 0,
        comm_pr: 0,
        comm_calc_day: 0,
        comm_calc_id: 0,
        under_sales_amt: 0,
        begin_dt: undefined,
        ship_out_dt: undefined,
        ship_out_stat: 0,
        is_old_auth: false,
        use_saleslip_prov: false,
        use_saleslip_sell: false,
        installment_limit: 0,
        note: ''
    }) 
    return {
        path, item
    }
});
