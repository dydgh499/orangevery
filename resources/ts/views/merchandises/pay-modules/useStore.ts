import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { Options, PayModule } from '@/views/types';

export const module_types = <Options[]>([
    { id: 0, title: "단말기" }, { id: 1, title: "수기결제" },
    { id: 2, title: "인증결제" }, { id: 3, title: "간편결제" },
])
export const installments = <Options[]>([
    { id: 0, title: "일시불" }, { id: 2, title: "2개월" },
    { id: 3, title: "3개월" }, { id: 4, title: "4개월" },
    { id: 5, title: "5개월" }, { id: 6, title: "6개월" },
    { id: 7, title: "7개월" }, { id: 8, title: "8개월" },
    { id: 9, title: "9개월" }, { id: 10, title: "10개월" },
    { id: 11, title: "11개월" }, { id: 12, title: "12개월" },
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

    const headers: Record<string, string> = {
        'id' : 'NO.',
        'mcht_name' : '가맹점 상호',
        'note' : '별칭',
        'module_type' : '모듈타입',
        'pg_id' : 'PG사명',
        'ps_id' : '구간',
        'settle_type' : '정산일',
        'mid' : 'MID',
        'tid' : 'TID',
        'installment' : '할부한도',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
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
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_nm as string
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

export const useUpdateStore = defineStore('payModUpdateStore', () => {   
    const path  = 'merchandises/pay-modules'
    const item  = reactive<PayModule>({
        id: 0,
        mcht_id: 0,
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
        ship_out_stat: false,
        is_old_auth: false,
        use_saleslip_prov: false,
        use_saleslip_sell: false,
        installment: 0,
        note: '비고',
        settle_prem: null
    })
    //카드사 필터 및 다른 필터옵션들
    return {
        path, item
    }
});
