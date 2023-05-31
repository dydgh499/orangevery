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

export const useSalesFilterStore = defineStore('salesFilterStore', () => {
    const sales = Array.from({ length: 6 }, () => ref<any[]>([]));    
    const errorHandler = <any>(inject('$errorHandler'))

    onMounted(async () => {
        await classification()
    })
    const classification = async () => {
        try {
            const id = 0;
            const r = await axios.get('/api/v1/manager/salesforces/classification', { params: { id: id } })
            const classes = ['하위대리점', '대리점', '하위총판', '총판', '하위지사', '지사']
            for (let index = 0; index < sales.length; index++) {
                r.data['class_'+index].unshift({id:null, nick_name: classes[index]+' 선택'})
                sales[index].value = r.data['class_'+index]
            }
        }
        catch (e) {
            const res = errorHandler(e)
            console.log(res)
        }
    }
    return {
        sales
    }
})

export const useUpdateStore = defineStore('salesUpdateStore', () => {
    const path  = 'salesforces'
    const item  = reactive<Salesforce>({
        id: 0,
        tax_type: 0,
        created_at: undefined,
        brand_id: 0,
        user_name: '',
        user_pw: '',
        nick_name: '',
        addr: '',
        phone_num: '',
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
        acct_bank_cd: '',
        updated_at: undefined,
        id_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined
    })
    return {
        path, item
    }
})
