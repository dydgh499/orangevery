import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import { MchtOption, Merchandise } from '@/views/types';
import corp from '@corp';

export const useSearchStore = defineStore('mchtSearchStore', () => {
    const store = Searcher('merchandises')
    const head = Header('merchandises', '가맹점 관리')
    const levels = corp.pv_options.auth.levels

    const headers: Record<string, string> = {
        'id': 'NO.',
    }
    if (levels.sales5_use) {
        headers['sales5_name'] = levels.sales5_name + ' ID'
        headers['sales5_fee'] = '수수료'
    }
    if (levels.sales4_use) {
        headers['sales4_name'] = levels.sales4_name + ' ID'
        headers['sales4_fee'] = '수수료'
    }
    if (levels.sales3_use) {
        headers['sales3_name'] = levels.sales3_name + ' ID'
        headers['sales3_fee'] = '수수료'
    }
    if (levels.sales2_use) {
        headers['sales2_name'] = levels.sales2_name + ' ID'
        headers['sales2_fee'] = '수수료'
    }
    if (levels.sales1_use) {
        headers['sales1_name'] = levels.sales1_name + ' ID'
        headers['sales1_fee'] = '수수료'
    }
    if (levels.sales0_use) {
        headers['sales0_name'] = levels.sales0_name + ' ID'
        headers['sales0_fee'] = '수수료'
    }
    headers['user_name'] = '가맹점 ID'
    headers['trx_fee'] = '수수료'
    headers['hold_fee'] = '유보금 수수료'
    headers['mcht_name'] = '상호'
    headers['nick_name'] = '대표자명'
    headers['phone_num'] = '연락처'
    headers['resident_num'] = '사업자등록번호'
    headers['business_num'] = '주민등록번호'

    headers['sector'] = '업종'
    headers['addr'] = '주소'
    headers['acct_bank_nm'] = '은행'
    headers['acct_bank_cd'] = '은행코드'
    headers['acct_nm'] = '예금주'
    headers['acct_num'] = '계좌번호'
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'

    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})

export const useUpdateStore = defineStore('mchtUpdateStore', () => {
    const path = 'merchandises'
    const item = reactive<Merchandise>({
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
