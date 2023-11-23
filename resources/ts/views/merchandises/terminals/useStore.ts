import { Header } from '@/views/headers'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getUserLevel } from '@axios'

export const useSearchStore = defineStore('terminalSearchStore', () => {
    const store = Searcher('merchandises/terminals')
    const head = Header('merchandises/terminals', '장비 관리')
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

    const headers1: Record<string, string> = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'note': '별칭',
        'module_type': '모듈타입',
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

    head.main_headers.value = [];
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
