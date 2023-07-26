import { Header } from '@/views/headers'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { Searcher } from '@/views/searcher'
import { Merchandise } from '@/views/types'
import { axios, user_info } from '@axios'
import corp from '@corp'

export const useSearchStore = defineStore('mchtSearchStore', () => {
    const store     = Searcher('merchandises')
    const head      = Header('merchandises', '가맹점 관리')
    const levels    = corp.pv_options.auth.levels
    const paid      = corp.pv_options.paid;

    const headers: Record<string, string> = {
        'id': 'NO.',
    }
    if (levels.sales5_use && user_info.value.level >= 30) {
        headers['sales5_name'] = levels.sales5_name
        headers['sales5_fee'] = '수수료'
    }
    if (levels.sales4_use && user_info.value.level >= 25) {
        headers['sales4_name'] = levels.sales4_name
        headers['sales4_fee'] = '수수료'
    }
    if (levels.sales3_use && user_info.value.level >= 20) {
        headers['sales3_name'] = levels.sales3_name
        headers['sales3_fee'] = '수수료'
    }
    if (levels.sales2_use && user_info.value.level >= 17) {
        headers['sales2_name'] = levels.sales2_name
        headers['sales2_fee'] = '수수료'
    }
    if (levels.sales1_use && user_info.value.level >= 15) {
        headers['sales1_name'] = levels.sales1_name
        headers['sales1_fee'] = '수수료'
    }
    if (levels.sales0_use && user_info.value.level >= 13) {
        headers['sales0_name'] = levels.sales0_name
        headers['sales0_fee'] = '수수료'
    }
    headers['user_name'] = '가맹점 ID'
    headers['trx_fee'] = '수수료'
    headers['hold_fee'] = '유보금 수수료'
    headers['mcht_name'] = '상호'
    if(user_info.value.level >= 35) {
        headers['mids'] = 'MID'
        headers['tids'] = 'TID'
        headers['module_types'] = '모듈타입'
    }
    headers['nick_name'] = '대표자명'
    headers['phone_num'] = '연락처'
    headers['resident_num'] = '주민등록번호'
    headers['business_num'] = '사업자등록번호'

    headers['sector'] = '업종'
    headers['addr'] = '주소'
    headers['acct_bank_name'] = '은행'
    headers['acct_bank_code'] = '은행코드'
    headers['acct_name'] = '예금주'
    headers['acct_num'] = '계좌번호'
    
    if (paid.subsidiary_use_control)
        headers['enabled'] = '전산사용여부'

    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'

    if (user_info.value.level >= 35)
        headers['extra_col'] = '더보기'
    
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const getModuleTypes = (my_modules: any[]) => {
        return my_modules.map(id => module_types.find(module => module.id === id)?.title )
    };

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            if(user_info.value.level >= 35) {
                datas[i]['module_types'] = getModuleTypes(datas[i]['module_types']).join(',')
                datas[i]['mids'] = datas[i]['mids'].join(',')
                datas[i]['tids'] = datas[i]['tids'].join(',')
            }
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
        getModuleTypes,
    }
})

export const defaultItemInfo = () => {
    const path = 'merchandises'
    const item = reactive<Merchandise>({
        id:0,
        acct_bank_code: null,
        acct_bank_name: '은행명',
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
        acct_name: '',
        created_at: null,
        updated_at: null,
        id_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined,
        sales5_id: null,
        sales4_id: null,
        sales3_id: null,
        sales2_id: null,
        sales1_id: null,
        sales0_id: null,
        sales5_fee: 0,
        sales4_fee: 0,
        sales3_fee: 0,
        sales2_fee: 0,
        sales1_fee: 0,
        sales0_fee: 0,
        // options
        custom_id: null,
        enabled: true,
        use_saleslip_prov: false,
        use_saleslip_sell: false,
        is_show_fee: false,
        note: ''
    })
    return {
        path, item
    }
}

export const getAllMerchandises = async(module_type:number|null = null) => {
    const url = '/api/v1/manager/merchandises/all' + (module_type != null ? '?module_type='+module_type : '')
    const r = await axios.get(url)
    return r.data.content
}
