import { Searcher } from '@/views/searcher';
import { MchtOption, Merchandise } from '@/views/types';

export const useSearchStore = defineStore('mchtSearchStore', () => {    
    const store = Searcher<Merchandise>('merchandises', <Merchandise>({}))
    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('지사 ID', 'sales5_name')
        store.setHeader('수수료 ', 'sales5_fee')
        store.setHeader('하위지사 ID', 'sales4_name')
        store.setHeader('수수료  ', 'sales4_fee')
        store.setHeader('총판 ID', 'sales3_name')
        store.setHeader('수수료   ', 'sales3_fee')
        store.setHeader('하위총판 ID', 'sales2_name')
        store.setHeader('수수료    ', 'sales2_fee')
        store.setHeader('대리점 ID', 'sales1_name')
        store.setHeader('수수료     ', 'sales1_fee')
        store.setHeader('하위대리점 ID', 'sales0_name')
        store.setHeader('수수료      ', 'sales0_fee')
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
        })
    })
    return {
        path, item
    }
})
