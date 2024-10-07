import { getFixplusMchtHeader, isFixplus } from '@/plugins/fixplus'
import { Header } from '@/views/headers'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { Merchandise, Options } from '@/views/types'
import { avatars } from '@/views/users/useStore'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'

export const tax_category_types = <Options[]>([
    {id:0, title:'과세'}, {id:1, title:'면세'}, 
])

const getMchtHeaders = (head :any) => {
    const getIdCol = () => {
        return {
            'id' : 'NO.'
        }
    }

    const getSettleHoldCols = () => {
        const headers_1:Record<string, string> = {}
        if (corp.pv_options.paid.use_settle_hold) {
            headers_1['settle_hold_s_dt'] = '지급보류 시작일'
            headers_1['settle_hold_reason'] = '지급보류 사유'
        }
        return headers_1
    }

    const getSalesforceCols = () => {
        const levels = corp.pv_options.auth.levels
        const headers_2:Record<string, string> = {}
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers_2['sales5_name'] = levels.sales5_name
            headers_2['sales5_fee'] = '수수료'
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers_2['sales4_name'] = levels.sales4_name
            headers_2['sales4_fee'] = '수수료'
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers_2['sales3_name'] = levels.sales3_name
            headers_2['sales3_fee'] = '수수료'
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers_2['sales2_name'] = levels.sales2_name
            headers_2['sales2_fee'] = '수수료'
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers_2['sales1_name'] = levels.sales1_name
            headers_2['sales1_fee'] = '수수료'
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers_2['sales0_name'] = levels.sales0_name
            headers_2['sales0_fee'] = '수수료'
        }
        return headers_2
    }

    const getMerchandiseCols = () => {
        const headers_3:Record<string, string> = {}
        headers_3['user_name'] = '가맹점 ID'
        headers_3['mcht_name'] = '상호'
        if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
            headers_3['trx_fee'] = '가맹점 수수료'
            headers_3['hold_fee'] = '유보금 수수료'
        }
        headers_3['sector'] = '업종'
        return headers_3
    }

    const getPaymentModuleCols = () => {
        const headers_4:Record<string, string> = {}
        headers_4['settle_types'] = '정산일'
        if(getUserLevel() >= 35) {
            headers_4['serial_nums'] = '시리얼번호'
            headers_4['mids'] = 'MID'
            headers_4['tids'] = 'TID'
            headers_4['module_types'] = '모듈타입'
            headers_4['pgs'] = 'PG사'
        }
        return headers_4
    }

    const getPrivacyCols = () => {
        const headers_5:Record<string, string> = {}
        headers_5['nick_name'] = '대표자명'
        headers_5['phone_num'] = '대표자 연락처'
        headers_5['contact_num'] = '사업장 연락처'
        headers_5['resident_num'] = '주민등록번호'
        headers_5['business_num'] = '사업자등록번호'
        headers_5['addr'] = '주소'
        return headers_5
    }

    const getBankCols = () => {
        const headers_6:Record<string, string> = {}
        if(((getUserLevel() == 10 && !user_info.value.is_hide_account) || getUserLevel() >= 13)) {
            headers_6['acct_bank_name'] = '은행'
            headers_6['acct_bank_code'] = '은행코드'
            headers_6['acct_name'] = '예금주'
            headers_6['acct_num'] = '계좌번호'    
        }
        return headers_6
    }

    const getNotiCols = () => {
        const headers_7:Record<string, string> = {}
        if(corp.pv_options.paid.use_noti && getUserLevel() >= 35) {
            headers_7['notis'] = '노티별칭'
        }
        return headers_7

    }

    const getSecureCols = () => {
        const headers_8:Record<string, string> = {}
        if (corp.pv_options.paid.subsidiary_use_control)
            headers_8['enabled'] = '전산사용여부'
        if(getUserLevel() >= 35 || (getUserLevel() >= 13 && user_info.value.is_able_unlock_mcht)) {
            headers_8['is_lock'] = '계정잠김여부'
            headers_8['locked_at'] = '계정잠금시간'
        }
        return headers_8
    }

    const getEtcCols = () => {
        const headers_9:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_9['custom_id'] = '커스텀필터'
            headers_9['note'] = '메모사항'
        }
        headers_9['created_at'] = '생성시간'
        headers_9['updated_at'] = '업데이트시간'
        if(getUserLevel() >= 35 || (getUserLevel() >= 13 && user_info.value.is_able_unlock_mcht)) 
            headers_9['extra_col'] = '더보기'
        return headers_9
    }
    
    const headers0:any = getIdCol()
    const headers1:any = getSettleHoldCols()
    const headers2:any = getSalesforceCols()
    const headers3:any = getMerchandiseCols()
    const headers4:any = getPaymentModuleCols()
    const headers5:any = getPrivacyCols()
    const headers6:any = getBankCols()
    const headers7:any = getNotiCols()
    const headers8:any = getSecureCols()
    const headers9:any = getEtcCols()

    const headers: Record<string, string> = {
        ...headers0,
        ...headers1,
        ...headers2,
        ...headers3,
        ...headers4,
        ...headers7,
        ...headers5,
        ...headers6,
        ...headers8,
        ...headers9,
    }
    const sub_headers: any = []
    head.getSubHeaderCol('NO.', headers0, sub_headers)
    head.getSubHeaderCol('지급보류', headers1, sub_headers)
    head.getSubHeaderCol('상위 영업점', headers2, sub_headers)
    head.getSubHeaderCol('가맹점 정보', headers3, sub_headers)
    head.getSubHeaderCol('결제모듈 정보', headers4, sub_headers)
    head.getSubHeaderCol('노티 정보', headers7, sub_headers)
    head.getSubHeaderCol('개인 정보', headers5, sub_headers)
    head.getSubHeaderCol('계좌 정보', headers6, sub_headers)
    head.getSubHeaderCol('보안 정보', headers8, sub_headers)
    head.getSubHeaderCol('기타 정보', headers9, sub_headers)
    return [headers, sub_headers]
}

export const useSearchStore = defineStore('mchtSearchStore', () => {
    const store     = Searcher('merchandises')
    const head      = Header('merchandises', '가맹점 관리')
    const { pgs, settle_types, cus_filters } = useStore()

    if(isFixplus()) {
        head.headers.value = head.initHeader(getFixplusMchtHeader(), {})
    }
    else {
        const [headers, sub_headers] = getMchtHeaders(head)
        head.sub_headers.value = sub_headers
        head.headers.value = head.initHeader(headers, {})
    }

    head.flat_headers.value = head.flatten(head.headers.value)
    const metas = ref(<any>([]))
    if (getUserLevel() > 10) {
        metas.value = [
            {
                icon: 'tabler-user-check',
                color: 'primary',
                title: '금월 추가된 가맹점',
                stats: '0',
                percentage: 0,
            },
            {
                icon: 'tabler-user-exclamation',
                color: 'error',
                title: '금월 감소한 가맹점',
                percentage: 0,
                stats: '0',
            },
            {
                icon: 'tabler-user-check',
                color: 'primary',
                title: '금주 추가된 가맹점',
                percentage: 0,
                stats: '0',
            },
            {
                icon: 'tabler-user-exclamation',
                color: 'error',
                title: '금주 감소한 가맹점',
                percentage: 0,
                stats: '0',
            },
        ]
    }

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            if(getUserLevel() >= 35) {
                datas[i]['module_types'] = datas[i]['payment_modules'].map(module => module_types.find(type => type.id === module.module_type)?.title).join(',')  
                datas[i]['serial_nums'] = datas[i]['payment_modules'].map(module => module.serial_num).join(',')
                datas[i]['pgs'] = datas[i]['payment_modules'].map(module => pgs.find(pg => pg.id === module.module_type)?.title).join(',')  
                datas[i]['mids'] = datas[i]['payment_modules'].map(module => module.mid).join(',')
                datas[i]['tids'] = datas[i]['payment_modules'].map(module => module.tid).join(',')                
            }
            if(corp.pv_options.paid.use_noti && getUserLevel() >= 35) {
                datas[i]['notis'] = datas[i]['notis'].map(noti => noti.note).join(',')                
            }

            datas[i]['settle_types'] = datas[i]['payment_modules'].map(module => settle_types.find(settle_type => settle_type.id === module.settle_type)?.title).join(',')  
            datas[i]['resident_num'] = datas[i]['resident_num_front'] + "-" + (corp.pv_options.free.resident_num_masking ? "*******" : datas[i]['resident_num_back'])
            datas[i]['custom_id'] = cus_filters.find(cus => cus.id === datas[i]['custom_id'])?.name as string
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})

export const defaultItemInfo = () => {
    const path = 'merchandises'
    const item = reactive<Merchandise>({
        id: 0,
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
        passbook_img: '',
        id_img: '',
        contract_img: '',
        bsin_lic_img: '',
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
        tax_category_type: 0,
        custom_id: null,
        enabled: 1,
        use_saleslip_prov: 1,
        use_saleslip_sell: 0,
        is_show_fee: Number(corp.pv_options.free.default.is_show_fee),
        note: '',
        dev_fee: 0,
        use_regular_card: 0,
        use_collect_withdraw: 0,
        collect_withdraw_fee: 0,
        withdraw_fee: 0,
        resident_num_front: '',
        resident_num_back: '',
        profile_img: avatars[Math.floor(Math.random() * avatars.length)],
        use_multiple_hand_pay: 0,
        use_noti: 0,
        settle_hold_s_dt: '',
        settle_hold_reason: '',
        is_hide_account: 0,
        website_url: '',
        email: '',
        contact_num: '',
        specified_time_disable_limit: 0,
        phone_auth_limit_count: 0,
    })
    return {
        path, item
    }
}
