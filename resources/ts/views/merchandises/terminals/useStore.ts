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

    const getDefaultCol = () => {
        const headers1:Record<string, string> = {
            'id' : 'NO.',
            'mcht_name' : '가맹점 상호',
            'note' : '별칭',
        }
        if(getUserLevel() > 10)
            headers1['module_type'] = '모듈타입'
        return headers1
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
            headers_4['settle_fee'] = '입금수수료'
        }
        if(getUserLevel() > 10) {
            headers_4['settle_type'] = '정산일'
            headers_4['mid'] = 'MID'
            headers_4['tid'] = 'TID'    
        }
        return headers_4
    }

    const getTerminalCols = () => {
        const headers_5:Record<string, string> = {}        
        if(getUserLevel() > 10) {
            headers_5['terminal_id'] = '장비타입'
        }
        headers_5['serial_num'] = '시리얼번호'
        if(getUserLevel() > 10) {
            headers_5['begin_dt'] = '개통일'
            headers_5['ship_out_dt'] = '출고일'
            headers_5['ship_out_stat'] = '출고상태'
        }
        return headers_5
    }

    const getCommCols = () => {
        const headers_6:Record<string, string> = {}        
        if(getUserLevel() > 10) {
            headers_6['comm_settle_fee'] = '통신비'
            headers_6['comm_settle_day'] = '정산일'
            headers_6['comm_settle_type'] = '정산타입'
            headers_6['comm_calc_level'] = '정산주체'
            headers_6['under_sales_amt'] = '매출미달 차감금'            
            headers_6['under_sales_type'] = '매출미달 적용타입'
        }
        return headers_6
    }
    
    const getOptionCols = () => {
        const headers_7:Record<string, string> = {}
        if(getUserLevel() > 10) {
            headers_7['installment'] = '할부 한도'
            headers_7['abnormal_trans_limit'] = '이상거래 한도'
            headers_7['pay_dupe_least'] = '중복거래 하한금'    
        }
        return headers_7

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
    const headers5:any = getOptionCols()
    const headers6:any = getCommCols()
    const headers7:any = getETCCols()
    const headers = {
        ...headers0,
        ...headers1,
        ...headers2,
        ...headers3,
        ...headers4,
        ...headers5,
        ...headers6,
        ...headers7,
    }

    const sub_headers: any = []
    head.getSubHeaderCol('기본 정보', headers0, sub_headers)
    head.getSubHeaderCol('상위 영업라인', headers1, sub_headers)
    head.getSubHeaderCol('PG사 정보', headers2, sub_headers)
    head.getSubHeaderCol('결제/취소 정보', headers3, sub_headers)
    head.getSubHeaderCol('단말기 정보', headers4, sub_headers)
    head.getSubHeaderCol('FDS 설정정보', headers5, sub_headers)
    head.getSubHeaderCol('통신비 정보', headers6, sub_headers)
    head.getSubHeaderCol('기타 정보', headers7, sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
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
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
});
