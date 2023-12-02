import { Header } from '@/views/headers'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Transaction } from '@/views/types'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'

export const useSearchStore = defineStore('transSettlesSalesPartSearchStore', () => {    
    const store = Searcher('transactions/settle/salesforces/part')
    const head  = Header('transactions/settle/salesforces/part', '영업점 부분정산관리')
    const { pgs, pss, terminals } = useStore()

    const levels = corp.pv_options.auth.levels
    const headers: Record<string, string> = {
        'id': 'NO.',
        'module_type': '거래 타입',
    }

    headers['trx_dttm'] = '거래 시간'
    headers['cxl_dttm'] = '취소 시간'
    headers['mcht_name'] = '가맹점 상호'
    headers['appr_num'] = '승인번호'
    headers['amount'] = '거래 금액'
    headers['installment'] = '할부'
    headers['acquirer'] = '매입사'
    headers['card_num'] = '카드번호'
    headers['profit'] = '정산금'

    if(getUserLevel() >= 35) {
        headers['pg_id'] = 'PG사'
        headers['ps_id'] = '구간'
        headers['ps_fee'] = '구간 수수료'
        headers['mcht_settle_type'] = '가맹점 정산타입'
    }
    if (levels.sales5_use && getUserLevel() >= 30) {
        headers['sales5_name'] = levels.sales5_name
        headers['sales5_fee'] = '수수료'
    }
    if (levels.sales4_use && getUserLevel() >= 25) {
        headers['sales4_name'] = levels.sales4_name
        headers['sales4_fee'] = '수수료'
    }
    if (levels.sales3_use && getUserLevel() >= 20) {
        headers['sales3_name'] = levels.sales3_name
        headers['sales3_fee'] = '수수료'
    }
    if (levels.sales2_use && getUserLevel() >= 17) {
        headers['sales2_name'] = levels.sales2_name
        headers['sales2_fee'] = '수수료'
    }
    if (levels.sales1_use && getUserLevel() >= 15) {
        headers['sales1_name'] = levels.sales1_name
        headers['sales1_fee'] = '수수료'
    }
    if (levels.sales0_use && getUserLevel() >= 13) {
        headers['sales0_name'] = levels.sales0_name
        headers['sales0_fee'] = '수수료'
    }

    if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
        headers['mcht_fee'] = '수수료'
        headers['hold_fee'] = '유보금 수수료'
    }

    if(getUserLevel() >= 35) {
        headers['custom_id'] = '커스텀필터'
        headers['terminal_id'] = '장비타입'
    }

    headers['trx_amount'] = '거래 수수료'
    headers['hold_amount'] = '유보금'
    headers['mcht_settle_fee'] = '입금 수수료'
    headers['total_trx_amount'] = '총 거래 수수료'
    
    if(getUserLevel() >= 13)
    {
        headers['mid'] = 'MID'
        headers['tid'] = 'TID'    
    }
    headers['issuer'] = '발급사'

    headers['buyer_name'] = '구매자명'
    headers['buyer_phone'] = '구매자 연락처'
    
    headers['item_name'] = '상품명'
    if(getUserLevel() >= 13) {
        headers['ord_num'] = '주문번호'
        headers['trx_id'] = '거래번호'
        headers['ori_trx_id'] = '원거래번호'
    }
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'
    
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const metas = ref([
        {
            icon: 'ic-outline-payments',
            color: 'primary',
            title: '승인액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'ic-outline-payments',
            color: 'error',
            title: '취소액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'ic-outline-payments',
            color: 'success',
            title: '매출액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'ic-outline-payments',
            color: 'warning',
            title: '정산액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
    ])

    const exporter = async (type: number) => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(type, r.data.content)
    }
    const printer = (type:number, datas: Transaction[]) => {
        const keys = Object.keys(head.flat_headers.value)
        for (let i = 0; i <datas.length; i++) {
            datas[i]['module_type'] = module_types.find(module_type => module_type['id'] === datas[i]['module_type'])?.title as string
            datas[i]['installment'] = installments.find(inst => inst['id'] === datas[i]['installment'])?.title as string
            datas[i]['pg_id'] = pgs.find(pg => pg['id'] === datas[i]['pg_id'])?.pg_name as string
            datas[i]['ps_id'] =  pss.find(ps => ps['id'] === datas[i]['ps_id'])?.name as string
            datas[i]['terminal_id'] = terminals.find(terminal => terminal['id'] === datas[i]['terminal_id'])?.name as string

            if(levels.sales5_use)
                datas[i]['sales5_fee'] = (datas[i]['sales5_fee'] * 100).toFixed(3)
            if(levels.sales4_use)
                datas[i]['sales4_fee'] = (datas[i]['sales4_fee'] * 100).toFixed(3)
            if(levels.sales3_use)
                datas[i]['sales3_fee'] = (datas[i]['sales3_fee'] * 100).toFixed(3)
            if(levels.sales2_use)
                datas[i]['sales2_fee'] = (datas[i]['sales2_fee'] * 100).toFixed(3)
            if(levels.sales1_use)
                datas[i]['sales1_fee'] = (datas[i]['sales1_fee'] * 100).toFixed(3)
            if(levels.sales0_use)
                datas[i]['sales0_fee'] = (datas[i]['sales0_fee'] * 100).toFixed(3)

            datas[i]['mcht_fee'] = (datas[i]['mcht_fee'] * 100).toFixed(3)
            datas[i]['hold_fee'] = (datas[i]['hold_fee'] * 100).toFixed(3)
            datas[i]['ps_fee'] = (datas[i]['ps_fee'] * 100).toFixed(3)
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }
    return {
        store,
        head,
        exporter,
        printer,
        metas,
    }
})
