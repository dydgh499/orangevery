import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options, PayModule, StringOptions } from '@/views/types'
import { axios, user_info } from '@axios'

export const simplePays = <StringOptions[]>([
    { id: "KAKAO", title: "카카오" }, { id: "NAVER", title: "네이버" },
])

export const abnormal_trans_limits = <Options[]>([
    { id: 0, title: "한도없음" }, { id: 200, title: "200만원" },
    { id: 300, title: "300만원" }, { id: 500, title: "500만원" },
])

export const module_types = <Options[]>([
    { id: 0, title: "장비" }, { id: 1, title: "수기결제" },
    { id: 2, title: "인증결제" }, { id: 3, title: "간편결제" },
    /*{ id: 4, title: "실시간 이체" },*/
])
export const installments = <Options[]>([
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },
])

export const shipOutStats = <Options[]>([
    { id: 0, title: "공장비" }, { id: 1, title: "입고" },
    { id: 2, title: "출고" }, { id: 3, title: "해지" },
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

    const headers1: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'note' : '별칭',
        'module_type' : '모듈타입',
    }
    if(user_info.value >= 35)
    {
        headers1['pg_id'] = 'PG사명'
        headers1['ps_id'] = '구간'
    }
    const headers2: Record<string, string> = {
        'settle_type' : '정산일',
        'mid' : 'MID',
        'tid' : 'TID',
        'pay_key' : '결제 KEY',
        'installment' : '할부한도',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    const headers = {
        ...headers1,
        ...headers2,
    }
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['settle_type'] = settle_types.find(settle_type => settle_type['id'] === datas[i]['settle_type'])?.name as string

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
});

export const defaultItemInfo =  () => {   
    const path  = 'merchandises/pay-modules'
    const item  = reactive<PayModule>({
        id: 0,
        mcht_id: null,
        pg_id: 0,
        ps_id: 0,
        terminal_id: 0,
        settle_type: 0,
        module_type: 0,
        api_key: '',
        sub_key: '',
        mid: '',
        tid: '',
        serial_num: '',
        comm_settle_fee: 0,
        comm_settle_type: 1,
        comm_calc_level: 10,
        under_sales_amt: 0,
        begin_dt: undefined,
        ship_out_dt: undefined,
        ship_out_stat: 0,
        is_old_auth: false,
        installment: 0,
        note: '결제모듈 명칭을 적어주세요.😀',
        settle_fee: 0,
        pay_dupe_limit: 0,
        abnormal_trans_limit: 0,
        pay_year_limit: 0,
        pay_month_limit: 0,
        pay_day_limit: 0,
        pay_disable_s_tm: null,
        pay_disable_e_tm: null,
        show_pay_view: false,
        pay_key: '',
        filter_issuers: []
    })
    //카드사 필터 및 다른 필터옵션들
    return {
        path, item
    }
}

export const getAllPayModules = async(mcht_id:number|null=null) => {
    const url = '/api/v1/manager/merchandises/pay-modules/all' + (mcht_id != null ? '?mcht_id='+mcht_id : '')
    const r = await axios.get(url)
    return r.data.content
}
