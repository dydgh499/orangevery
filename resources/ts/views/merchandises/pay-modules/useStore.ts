import { Header } from '@/views/headers'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options, PayModule, StringOptions } from '@/views/types'
import { axios, getUserLevel, pay_token, user_info } from '@axios'
import corp from '@corp'

export const pay_window_secure_levels = <Options[]>([
    { id: 0, title: "결제창 숨김" }, { id: 1, title: "결제창 노출" },
    { id: 2, title: "PIN 인증" }, { id: 3, title: "SMS 인증" },
    { id: 4, title: "SCA 인증" }
])

export const simplePays = <StringOptions[]>([
    { id: "KAKAO", title: "카카오페이" }, 
    { id: "NAVER", title: "네이버페이" }, 
    { id: "APPLE", title: "애플페이"},
])

export const module_types = <Options[]>([
    { id: 0, title: "장비" }, { id: 1, title: "수기결제" },
    { id: 2, title: "인증결제" }, { id: 3, title: "간편결제" },
])
if(corp.pv_options.paid.use_bill_key)
    module_types.push({ id: 4, title: "빌키결제" })

export const installments = <Options[]>([
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },
])

export const pay_window_extend_hours = <Options[]>([
    { id: 1, title: "1시간" }, { id: 2, title: "2시간" },
    { id: 3, title: "3시간" }, { id: 4, title: "4시간" },
    { id: 5, title: "5시간" }, { id: 6, title: "6시간" },
    { id: 7, title: "7시간" }, { id: 8, title: "8시간" },
    { id: 9, title: "9시간" }, { id: 10, title: "10시간" },
    { id: 11, title: "11시간" }, { id: 12, title: "12시간" },
    { id: 25, title: "제한없음"}
])


export const ship_out_stats = <Options[]>([
    { id: 0, title: "공장비" }, { id: 1, title: "입고" },
    { id: 2, title: "출고" }, { id: 3, title: "해지" },
])

export const under_sales_types = <Options[]>([
    {id: 0, title:'적용안함'},
    {id: 1, title:'작월 1일 ~ 작월 말일'}, 
    {id: 2, title:'D-30 ~ 정산일'}, 
])
export const comm_settle_types = <Options[]>([
    {id: 0, title:'개통월 기준'},
    {id: 1, title:'개통월 M+1'},
    {id: 2, title:'개통월 M+2'},
])

export const cxl_types = <Options[]>([
    {id: 0, title:'취소금지'},
    {id: 1, title:'결제이후 +5분허용'},
    {id: 2, title:'당일허용'},
    {id: 3, title:'정산전까지 허용'},
])
if(corp.pv_options.paid.use_cancel_all_allow)
    cxl_types.push({id:-1, title: '모두허용'})

export const pay_limit_types = <Options[]>([
    {id: 0, title:'설정안함'},
    {id: 1, title:'주말 결제금지'},
    {id: 2, title:'공휴일 결제금지'},
    {id: 3, title:'주말+공휴일 결제금지'},
])

export const withdraw_limit_types = <Options[]>([
    {id: 0, title:'설정안함'},
    {id: 1, title:'주말 출금금지'},
    {id: 2, title:'공휴일 출금금지'},
    {id: 3, title:'주말+공휴일 출금금지'},
])

export const payModFilter = (all_pay_modules:PayModule[], filter:PayModule[], pmod_id:number|null) => {
    if (all_pay_modules.length > 0) {
        if (filter.length > 0) {
            let item = all_pay_modules.find((item:PayModule) => item.id === pmod_id)
            if (item != undefined && filter[0].mcht_id != item.mcht_id) {
                if (pmod_id != null)
                    pmod_id = null
            }
        }
        else {
            if (pmod_id != null)
                pmod_id = null
        }
    }
    return pmod_id
}
export const useSearchStore = defineStore('payModSearchStore', () => {    
    const store = Searcher('merchandises/pay-modules')
    const head  = Header('merchandises/pay-modules', '결제모듈 관리')
    const { findSalesName } = useSalesFilterStore()
    const levels = corp.pv_options.auth.levels

    const getDefaultCol = () => {
        return {
            'id' : 'NO.',
            'mcht_name' : '가맹점 상호',
            'note' : '별칭',
            'module_type': '모듈타입',
        }
    }

    const getSalesforceCols = () => {
        const levels = corp.pv_options.auth.levels
        const headers_2:Record<string, string> = {}
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers_2['sales5_id'] = levels.sales5_name
            headers_2['sales5_fee'] = '수수료'
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers_2['sales4_id'] = levels.sales4_name
            headers_2['sales4_fee'] = '수수료'
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers_2['sales3_id'] = levels.sales3_name
            headers_2['sales3_fee'] = '수수료'
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers_2['sales2_id'] = levels.sales2_name
            headers_2['sales2_fee'] = '수수료'
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers_2['sales1_id'] = levels.sales1_name
            headers_2['sales1_fee'] = '수수료'
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers_2['sales0_id'] = levels.sales0_name
            headers_2['sales0_fee'] = '수수료'
        }
        return headers_2
    }

    const getPGCols = () => {
        const headers_3:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_3['pg_id'] = 'PG사명'
            headers_3['ps_id'] = '구간'
        }
        return headers_3
    }

    const getPaymentCols = () => {
        const headers_4:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_4['settle_fee'] = '건별 수수료'
            headers_4['cxl_type'] = '취소타입'
        }
        if(getUserLevel() > 10) {
            headers_4['settle_type'] = '정산일'
            headers_4['mid'] = 'MID'
            headers_4['tid'] = 'TID'    
        }
        return headers_4
    }

    const getTerminalCols = () => {
        const headers_4:Record<string, string> = {}
        headers_4['terminal_id'] = '장비타입'
        headers_4['serial_num'] = '시리얼번호'
        headers_4['comm_settle_fee'] = '통신비'
        headers_4['comm_settle_day'] = '통신비 정산일'
        return headers_4
    }

    const getAbnormalCols = () => {
        return {
            'installment' : '할부 한도',
            'abnormal_trans_limit' : '이상거래 한도',
            'pay_dupe_least' : '중복거래 하한금',
        }
    }

    const getOptionCols = () => {
        const headers_6:Record<string, string> = {}
        if(corp.pv_options.paid.use_dup_pay_validation)
            headers_6['pay_dupe_limit'] = '동일카드 결제'
        headers_6['pay_limit_type'] = '결제금지타입'
        headers_6['pay_disable_tm'] = '결제금지 시간'
        headers_6['payment_term_min'] = '결제허용 간격'
        return headers_6
    }
    
    const getLimitCols = () => {
        const headers_7:Record<string, string> = {}
        headers_7['pay_year_limit'] = '연'
        headers_7['pay_month_limit'] = '월'
        headers_7['pay_day_limit'] = '일'
        headers_7['pay_single_limit'] = '단건'
        return headers_7
    }
    const getRealtimeCols = () => {
        const headers_8:Record<string, string> = {}
        if(getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit) {
            headers_8['use_realtime_deposit'] = '출금 사용여부'
            // TODO:: 정산지갑정보
        }
        return headers_8
    }
    
    const getPayWindowCols = () => {
        const headers_9:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_9['pay_window_secure_level'] = '보안등급'
            headers_9['pay_window_extend_hour'] = '연장시간'
        }
        return headers_9
    }

    const getContractCols = () => {
        return {
            'contract_s_dt': '시작일',
            'contract_e_dt': '종료일',
        }
    }

    const getETCCols = () => {
        return {
            'created_at' : '생성시간',
            'updated_at' : '업데이트시간',
        }
    }   

    const headers0:any = getDefaultCol()
    const headers1:any = getSalesforceCols()
    const headers2:any = getPGCols()
    const headers3:any = getPaymentCols()
    const headers4:any = getTerminalCols()
    const headers5:any = getAbnormalCols()
    const headers6:any = getOptionCols()
    const headers7:any = getLimitCols()
    const headers8:any = getRealtimeCols()
    const headers9:any = getPayWindowCols()    
    const headers10:any = getContractCols()
    const headers11:any = getETCCols()

    const headers = {
        ...headers0,
        ...headers1,
        ...headers2,
        ...headers3,
        ...headers4,
        ...headers5,
        ...headers6,
        ...headers7,
        ...headers8,
        ...headers9,
        ...headers10,
        ...headers11,
    }
    const sub_headers: any = []
    head.getSubHeaderCol('기본 정보', headers0, sub_headers)
    head.getSubHeaderCol('상위 영업라인', headers1, sub_headers)
    head.getSubHeaderCol('PG사 정보', headers2, sub_headers)
    head.getSubHeaderCol('결제/취소 정보', headers3, sub_headers)
    head.getSubHeaderCol('단말기 정보', headers4, sub_headers)
    head.getSubHeaderCol('FDS 설정정보', headers5, sub_headers)
    head.getSubHeaderCol('제한 정보', headers6, sub_headers)
    head.getSubHeaderCol('결제한도', headers7, sub_headers)
    head.getSubHeaderCol('실시간 정보', headers8, sub_headers)
    head.getSubHeaderCol('결제창 정보', headers9, sub_headers)
    head.getSubHeaderCol('계약 정보', headers10, sub_headers)
    head.getSubHeaderCol('기타 정보', headers11, sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    const { pgs, pss, settle_types, terminals, finance_vans } = useStore()

    const metas = ref(<any>[])
    if(getUserLevel() > 10) {
        metas.value = [
            {
                icon: 'tabler-user-check',
                color: 'primary',
                title: '금월 추가된 결제모듈',
                stats: '0',
                percentage: 0,
            },
            {
                icon: 'tabler-user-exclamation',
                color: 'error',
                title: '금월 감소한 결제모듈',
                percentage: 0,
                stats: '0',
            },
            {
                icon: 'tabler-user-check',
                color: 'primary',
                title: '금주 추가된 결제모듈',
                percentage: 0,
                stats: '0',
            },
            {
                icon: 'tabler-user-exclamation',
                color: 'error',
                title: '금주 감소한 결제모듈',
                percentage: 0,
                stats: '0',
            },
        ]
    }

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] = pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['settle_type'] = settle_types.find(settle_type => settle_type['id'] === datas[i]['settle_type'])?.name as string
            datas[i]['comm_settle_type'] = comm_settle_types.find(obj => obj.id === datas[i]['comm_settle_type'])?.title
            datas[i]['terminal_id'] = terminals.find(obj => obj.id === datas[i]['terminal_id'])?.title
            datas[i]['pay_disable_tm'] = datas[i].pay_disable_s_tm + "~" + datas[i].pay_disable_e_tm
            datas[i]['use_realtime_deposit'] = datas[i].use_realtime_deposit ? '사용' : '미사용'
            datas[i]['pay_window_secure_level'] = pay_window_secure_levels.find(obj => obj.id === datas[i].pay_window_secure_level)?.title
            datas[i]['pay_window_extend_hour'] = pay_window_extend_hours.find(obj => obj.id === datas[i].pay_window_extend_hour)?.title

            if (levels.sales5_use && getUserLevel() >= 30) {
                datas[i]['sales5_id'] = findSalesName('sales5_id', datas[i]['sales5_id'])
                datas[i]['sales5_fee'] = (datas[i]['sales5_fee'] * 100).toFixed(3)
            }
            if (levels.sales4_use && getUserLevel() >= 25) {
                datas[i]['sales4_id'] = findSalesName('sales4_id', datas[i]['sales4_id'])
                datas[i]['sales4_fee'] = (datas[i]['sales4_fee'] * 100).toFixed(3)
            }
            if (levels.sales3_use && getUserLevel() >= 20) {
                datas[i]['sales3_id'] = findSalesName('sales3_id', datas[i]['sales3_id'])
                datas[i]['sales3_fee'] = (datas[i]['sales3_fee'] * 100).toFixed(3)
            }
            if (levels.sales2_use && getUserLevel() >= 17) {
                datas[i]['sales2_id'] = findSalesName('sales2_id', datas[i]['sales2_id'])
                datas[i]['sales2_fee'] = (datas[i]['sales2_fee'] * 100).toFixed(3)
            }
            if (levels.sales1_use && getUserLevel() >= 15) {
                datas[i]['sales1_id'] = findSalesName('sales1_id', datas[i]['sales1_id'])
                datas[i]['sales1_fee'] = (datas[i]['sales1_fee'] * 100).toFixed(3)
            }
            if (levels.sales0_use && getUserLevel() >= 13) {
                datas[i]['sales5_id'] = findSalesName('sales0_id', datas[i]['sales0_id'])
                datas[i]['sales0_fee'] = (datas[i]['sales0_fee'] * 100).toFixed(3)
            }
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
});

export const defaultItemInfo =  () => {   
    const path  = 'merchandises/pay-modules'
    const item  = reactive<PayModule>({
        id: 0,
        mcht_id: null,
        pg_id: null,
        ps_id: null,
        terminal_id: null,
        settle_type: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        comm_settle_fee: 0,
        comm_settle_day: 1,
        comm_settle_type: 0,
        comm_calc_level: 10,
        under_sales_amt: 0,
        under_sales_type: 0,
        under_sales_limit: 0,
        begin_dt: null,
        ship_out_dt: null,
        ship_out_stat: 0,
        is_old_auth: 0,
        installment: Number(corp.pv_options.free.default.installment),
        note: '장비',
        settle_fee: 0,
        pay_dupe_limit: 0,
        abnormal_trans_limit: Number(corp.pv_options.free.default.abnormal_trans_limit),
        pay_year_limit: 0,
        pay_month_limit: 0,
        pay_day_limit: 0,
        pay_single_limit: 0,
        pay_disable_s_tm: '00:00:00',
        pay_disable_e_tm: '00:00:00',
        pay_window_secure_level: 0,
        pay_key: '',
        filter_issuers: [],
        contract_s_dt: null,
        contract_e_dt: null,
        cxl_type: 2,
        use_realtime_deposit: 0,
        pay_dupe_least: 0,
        payment_term_min: 1,
        p_mid: '',
        pay_window_extend_hour: 1,
        is_different_settlement: 1,
        pay_limit_type: 0,
        va_id: null,
        last_settle_month: 0,
    })
    //카드사 필터 및 다른 필터옵션들
    return {
        path, item
    }
}

export const getAllPayModules = async(mcht_id:number|null=null, module_type:number|null=null) => {    
    let url = '/api/v1/manager/merchandises/pay-modules/all'
    const params = <any>({})
    if(mcht_id || module_type) {
        if(mcht_id)
            params['mcht_id'] = mcht_id
        if(module_type)
            params['module_type'] = module_type
        url += "?" + new URLSearchParams(params)
    }

    try {
        const r = await axios.get(url)
        return r.data.content.sort((a:PayModule, b:PayModule) => a.note.localeCompare(b.note))
    }
    catch (e: any) {
        pay_token.value = ''
        user_info.value = {}
        location.href = '/'
        return []
    }
}
