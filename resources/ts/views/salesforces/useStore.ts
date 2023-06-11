import { Searcher } from '@/views/searcher';
import type { Options, Salesforce } from '@/views/types';
import { axios } from '@axios';
import corp from '@corp';

const levels = corp.pv_options.auth.levels

export const salesLevels = () => {
    const sales = <Options[]>([]);
    if(levels.sales0_use)
        sales.push({id: 13, title: levels.sales0_name})
    if(levels.sales1_use)
        sales.push({id: 15, title: levels.sales1_name})
    if(levels.sales2_use)
        sales.push({id: 17, title: levels.sales2_name})
    if(levels.sales3_use)
        sales.push({id: 20, title: levels.sales3_name})
    if(levels.sales4_use)
        sales.push({id: 25, title: levels.sales4_name})
    if(levels.sales5_use)
        sales.push({id: 30, title: levels.sales5_name})
    return sales
}

export const allLevels = () => {
    const sales = salesLevels()
    sales.unshift({id: 10, title: '가맹점'})
    sales.push({id: 40, title: '본사'})
    if(levels.dev_use)
        sales.push({id: 50, title: levels.dev_name})
    return sales
}

export const useSearchStore = defineStore('salesSearchStore', () => {
    const store = Searcher<Salesforce>('salesforces', <Salesforce>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('등급', 'level')
        store.setHeader('영업점 ID', 'user_name')
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
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})

export const useSalesFilterStore = defineStore('salesFilterStore', () => {
    const sales = Array.from({ length: 6 }, () => ref<any[]>([]));
    onMounted(async () => {
        await classification()
    })
    const classification = async () => {
        const r = await axios.get('/api/v1/manager/salesforces/classification')
        const barnd_sales = salesLevels()
        const keys = Object.keys(r.data);
        for (let index = 0; index < keys.length; index++) {
            var level = Number(keys[index].replace('level_', ''))
            var temp = barnd_sales.find(item => item.id === level)
            if(temp != undefined)
                r.data[keys[index]].unshift({ id: null, nick_name: temp.title + ' 선택' })
                
            sales[index].value = r.data['level_' + level]
        }
    }
    return {
        sales
    }
})

export const useUpdateStore = defineStore('salesUpdateStore', () => {
    const path = 'salesforces'
    const item = reactive<Salesforce>({
        id: 0,
        tax_type: 0,
        created_at: undefined,
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
        bsin_lic_file: undefined,
        level: 13,
    })
    return {
        path, item
    }
})
