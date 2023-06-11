import { Searcher } from '@/views/searcher';
import { MchtOption, Merchandise } from '@/views/types';
import corp from '@corp';

export const useSearchStore = defineStore('mchtSearchStore', () => {    
    const store = Searcher<Merchandise>('merchandises', <Merchandise>({}))
    const levels = corp.pv_options.auth.levels

    function setHeaders() {
        store.setHeader('NO.', 'id')
        if(levels.sales5_use)
        {
            store.setHeader(levels.sales5_name+' ID', 'sales5_name')
            store.setHeader('수수료 ', 'sales5_fee')    
        }
        if(levels.sales4_use)
        {
            store.setHeader(levels.sales4_name+' ID', 'sales4_name')
            store.setHeader('수수료  ', 'sales4_fee')    
        }
        if(levels.sales3_use)
        {
            store.setHeader(levels.sales3_name+' ID', 'sales3_name')
            store.setHeader('수수료   ', 'sales3_fee')    
        }
        if(levels.sales2_use)
        {
            store.setHeader(levels.sales2_name+' ID', 'sales2_name')
            store.setHeader('수수료    ', 'sales2_fee')    
        }
        if(levels.sales1_use)
        {
            store.setHeader(levels.sales1_name+' ID', 'sales1_name')
            store.setHeader('수수료     ', 'sales1_fee')    
        }
        if(levels.sales0_use)
        {
            store.setHeader(levels.sales0_name+' ID', 'sales0_name')
            store.setHeader('수수료      ', 'sales0_fee')    
        }
        store.setHeader('가맹점 ID', 'user_name')
        store.setHeader('수수료       ', 'trx_fee')
        store.setHeader('유보금 수수료', 'hold_fee')
        store.setHeader('상호', 'mcht_name')
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

export const useUpdateStore = defineStore('mchtUpdateStore', () => {
    const path  = 'merchandises'
    const item  = reactive<Merchandise>({
        acct_bank_cd: '000',
        acct_bank_nm: '은행명',
        hold_fee: 0,
        trx_fee: 0,
        mcht_name: '',
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
        id: 0,
        created_at: null,
        updated_at: null,
        id_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined,
        sales5_id: 0,
        sales5_fee: undefined,
        sales4_id: 0,
        sales4_fee: undefined,
        sales3_id: 0,
        sales3_fee: undefined,
        sales2_id: 0,
        sales2_fee: undefined,
        sales1_id: 0,
        sales1_fee: undefined,
        sales0_id: 0,
        sales0_fee: undefined,
        enabled: false,
        pv_options: reactive<MchtOption>({
            abnormal_trans_limit: 0,
            pay_day_limit: 0,
            pay_month_limit: 0,
            pay_year_limit: 0,
            pay_dupe_limit: 0,
            is_show_fee: false,
        }),
        custom_id: null
    })
    return {
        path, item
    }
})
