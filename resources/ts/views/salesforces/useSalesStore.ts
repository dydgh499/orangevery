import { Searcher } from '@/views/searcher';
import type { Salesforce } from '@/views/types';
import { Updater } from '@/views/updater';


export const useSearchStore = defineStore('salesSearchStore', () => {    
    const store = Searcher<Salesforce>('salesforces', <Salesforce>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('상위 영업자 ID/수수료', 'group_name')
        store.setHeader('영업자 ID/수수료', 'user_name')
        store.setHeader('대표자명', 'nick_name')
        store.setHeader('연락처', 'phone_num')
        store.setHeader('사업자등록번호', 'resident_num')
        store.setHeader('주민등록번호', 'business_num')
        store.setHeader('업종', 'sector')
        store.setHeader('주소', 'addr')
        store.setHeader('은행', 'acct_bank_nm')
        store.setHeader('은행코드', 'acct_bank_cd')
        store.setHeader('예금주', 'acct_nm')
        store.setHeader('계좌번호', 'acct_num')
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

export const useUpdateStore = defineStore('salesUpdateStore', () => {    
    const store = Updater<Salesforce>('salesforces', <Salesforce>({}))
    return {
        store,
    }
});
