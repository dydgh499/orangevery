import { Header } from '@/views/headers'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getUserLevel } from '@axios'
import corp from '@corp'

export const useSearchStore = defineStore('terminalSearchStore', () => {
    const store = Searcher('merchandises/terminals')
    const head = Header('merchandises/terminals', '장비 관리')
    const { pgs, pss, settle_types, terminals } = useStore()
    const { findSalesName } = useSalesFilterStore()
    const levels = corp.pv_options.auth.levels

    const headers1: Record<string, string> = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'note': '별칭',
        'module_type': '모듈타입',
    }

    if (levels.sales5_use && getUserLevel() >= 30) {
        headers1['sales5_id'] = levels.sales5_name
        headers1['sales5_fee'] = '수수료'
    }
    if (levels.sales4_use && getUserLevel() >= 25) {
        headers1['sales4_id'] = levels.sales4_name
        headers1['sales4_fee'] = '수수료'
    }
    if (levels.sales3_use && getUserLevel() >= 20) {
        headers1['sales3_id'] = levels.sales3_name
        headers1['sales3_fee'] = '수수료'
    }
    if (levels.sales2_use && getUserLevel() >= 17) {
        headers1['sales2_id'] = levels.sales2_name
        headers1['sales2_fee'] = '수수료'
    }
    if (levels.sales1_use && getUserLevel() >= 15) {
        headers1['sales1_id'] = levels.sales1_name
        headers1['sales1_fee'] = '수수료'
    }
    if (levels.sales0_use && getUserLevel() >= 13) {
        headers1['sales0_id'] = levels.sales0_name
        headers1['sales0_fee'] = '수수료'
    }
    if(getUserLevel() >= 35)
    {
        headers1['pg_id'] = 'PG사명'
        headers1['ps_id'] = '구간'
        headers1['settle_fee'] = '입금수수료'        
    }
    const headers2: Record<string, string> = {
        'settle_type': '정산일',
        'mid': 'MID',
        'tid': 'TID',
        'installment': '할부한도',
        'terminal_id': '장비 타입',
        'serial_num': '시리얼 번호',
        'comm_settle_fee': '통신비',
        'comm_settle_day': '통신비 정산일',
        'comm_calc_level': '통신비 정산주체',
        'under_sales_amt': '매출미달 차감금',
        'under_sales_type': '매출미달 적용타입',
        'begin_dt': '개통일',
        'ship_out_dt': '출고일',
        'ship_out_stat': '출고상태',
        'created_at': '생성시간',
        'updated_at': '업데이트시간',
    }    
    const headers = {
        ...headers1,
        ...headers2,
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['settle_type'] = settle_types.find(settle_type => settle_type['id'] === datas[i]['settle_type'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string

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
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
});
