import { Searcher } from '@/views/searcher';
import type { PayModule } from '@/views/types';

export const useSearchStore = defineStore('terminalSearchStore', () => {    
    const store = Searcher<PayModule>('merchandises/terminals', <PayModule>({}))
    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('가맹점 상호', 'mcht_name')        
        store.setHeader('별칭', 'note')
        store.setHeader('모듈타입', 'module_type')
        store.setHeader('PG사명', 'pg_id')
        store.setHeader('구간', 'ps_id')
        store.setHeader('결제조건', 'pay_cond_id')
        store.setHeader('MID', 'mid')
        store.setHeader('TID', 'tid')
        store.setHeader('할부한도', 'installment')
        store.setHeader('단말기 타입', 'terminal_id')        
        store.setHeader('시리얼 번호', 'serial_num')
        store.setHeader('통신비', 'comm_pr')
        store.setHeader('통신비 정산일', 'comm_calc_day')
        store.setHeader('통신비 정산주체', 'comm_calc_level')
        store.setHeader('매출미달 차감금', 'under_sales_amt')
        store.setHeader('개통일', 'begin_dt')
        store.setHeader('출고일', 'ship_out_dt')
        store.setHeader('출고상태', 'ship_out_stat')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')    
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
});
