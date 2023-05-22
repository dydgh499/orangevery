import { Searcher } from '@/views/searcher';
import type { Salesforce } from '@/views/types';
import { axios } from '@axios';

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
    return {
        store,
        setHeaders,
    }
})

export const useSalesHierarchicalStore = defineStore('salesHierarchicalStore', () => {
    const hierarchical = ref<any[]>([])
    const flattened = ref<any[]>([])
    const errorHandler = <any>(inject('$errorHandler'))

    onMounted(async () => {
        const flattening = (items: any[]): any[] => {
            let flattenedItems = []
            for (const item of items) {
                flattenedItems.push(item)
                if (item.children)
                    flattenedItems = flattenedItems.concat(flattening(item.children));
            }
            return flattenedItems;
        }

        try {
            const r = await axios.get('/api/v1/manager/salesforces/hierarchical-down')
            Object.assign(hierarchical.value, r.data)
            Object.assign(flattened.value, flattening(hierarchical.value))
        }
        catch (e) {
            const res = errorHandler(e)
            console.log(res)
        }
    })
    const hierarchicalUp = async (mcht_id: number) => {
        try {
            const r = await axios.get('/api/v1/manager/salesforces/hierarchical-up', { params: { group_id: mcht_id } })
            return r.data   // 배열로 사용    
        }
        catch (e) {
            const res = errorHandler(e)
            console.log(res)
        }
    }
    const flattenUp = async (mcht_id: number) => {
        const flattening = (item: any): any[] => {
            const flattenedAncestors: any[] = [];
    
            while (item !== null) {
                flattenedAncestors.push(item);
                item = item.ancestors;
            }
            return flattenedAncestors.reverse();
        }

        const r = await axios.get('/api/v1/manager/salesforces/hierarchical-up', { params: { group_id: mcht_id } })
        return flattening(r.data)   // 배열로 사용
    }
    return {
        hierarchical, flattened, hierarchicalUp, flattenUp
    }
})

export const useUpdateStore = defineStore('salesUpdateStore', () => {
    const path  = 'salesforces'
    const item  = reactive<Salesforce>({
        id: 0,
        tax_type: 0,
        trx_fee: undefined,
        created_at: undefined,
        brand_id: 0,
        group_id: 0,
        user_name: '',
        user_pw: '',
        nick_name: '',
        addr: '',
        phone_num: '',
        email: '',
        resident_num: '',
        business_num: '',
        sector: '',
        passbook_img: null,
        id_img: null,
        contract_img: null,
        bsin_lic_img: null,
        acct_num: '',
        acct_nm: '',
        acct_bank_nm: '',
        acct_bank_cd: ''
    })
    return {
        path, item
    }
})
