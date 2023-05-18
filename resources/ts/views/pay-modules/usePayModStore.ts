import { Searcher } from '@/views/searcher';
import type { PayModule } from '@/views/types';
import { Updater } from '@/views/updater';

export const useSearchStore = defineStore('payModSearchStore', () => {    
    const store = Searcher<PayModule>('pay-modules', <PayModule>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('PG사명', 'pg_name')
        store.setHeader('구간', 'pg_sec_id')
        store.setHeader('출금타입', 'withdraw_name')
        store.setHeader('모듈타입', 'module_type')
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
    const store = Updater<PayModule>('pay-modules', <PayModule>({}))
    return {
        store,
    }
});
