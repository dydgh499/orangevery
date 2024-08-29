import { isFixplus } from '@/plugins/fixplus';
import { Header } from '@/views/headers';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { Transaction } from '@/views/types';
import { getUserLevel, user_info } from '@axios';
import corp from '@corp';

export const useSearchStore = defineStore('transSettlesSalesPartSearchStore', () => {    
    const store = Searcher('transactions/settle/salesforces/part')
    const head  = Header('transactions/settle/salesforces/part', '영업점 부분정산관리')
    const { pgs, pss, terminals } = useStore()

    const levels = corp.pv_options.auth.levels

    const getTransactionCols = () => {
        const headers_1:Record<string, string> = {}
        headers_1['id'] = 'NO.'
        if(isFixplus() === false) {
            headers_1['module_type'] = '거래 타입'
        }
        headers_1['trx_dttm'] = '거래 시간'
        headers_1['cxl_dttm'] = '취소 시간'
        headers_1['appr_num'] = '승인번호'
        headers_1['amount'] = '거래 금액'
        headers_1['installment'] = '할부'
        headers_1['acquirer'] = '매입사'
        headers_1['card_num'] = '카드번호'
        return headers_1
    }

    const getMerchandiseCols = () => {
        const headers_2:Record<string, string> = {}
        headers_2['user_name'] = '가맹점 ID'
        headers_2['mcht_name'] = '가맹점 상호'
        if(getUserLevel() >= 35) {
            headers_2['mcht_settle_type'] = '가맹점 정산타입'
        }
        if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
            headers_2['profit'] = '정산금'
        }
        return headers_2
    }

    const getPGCols = () => {
        const headers_3:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_3['pg_id'] = 'PG사'
            headers_3['ps_id'] = '구간'
            headers_3['ps_fee'] = '구간 수수료'
        }
        return headers_3
    }

    const getFeeCols = () => {
        const headers_4:Record<string, string> = {}
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers_4['sales5_name'] = levels.sales5_name
            headers_4['sales5_fee'] = '수수료'
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers_4['sales4_name'] = levels.sales4_name
            headers_4['sales4_fee'] = '수수료'
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers_4['sales3_name'] = levels.sales3_name
            headers_4['sales3_fee'] = '수수료'
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers_4['sales2_name'] = levels.sales2_name
            headers_4['sales2_fee'] = '수수료'
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers_4['sales1_name'] = levels.sales1_name
            headers_4['sales1_fee'] = '수수료'
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers_4['sales0_name'] = levels.sales0_name
            headers_4['sales0_fee'] = '수수료'
        }
    
        if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
            headers_4['mcht_fee'] = '가맹점 수수료'
            headers_4['hold_fee'] = '유보금 수수료'
            headers_4['trx_amount'] = '거래 수수료'
            headers_4['hold_amount'] = '유보금'
            headers_4['mcht_settle_fee'] = '입금 수수료'
            headers_4['total_trx_amount'] = '총 거래 수수료'
        }
        return headers_4
    }

    const getPrivacyCols = () => {
        const headers_5:Record<string, string> = {}
        headers_5['resident_num'] = '주민등록번호'
        headers_5['business_num'] = '사업자등록번호'
        headers_5['nick_name'] = '대표자명'

        if(getUserLevel() >= 35) {
            headers_5['custom_id'] = '커스텀필터'
            headers_5['terminal_id'] = '장비타입'
        }
        if(getUserLevel() >= 13) {
            headers_5['mid'] = 'MID'
            headers_5['tid'] = 'TID'
        }
        return headers_5
    }

    const getPaymentCols = () => {
        const headers_6:Record<string, string> = {}
        headers_6['issuer'] = '발급사'
        headers_6['buyer_name'] = '구매자명'
        headers_6['buyer_phone'] = '구매자 연락처'
        headers_6['item_name'] = '상품명'    
        return headers_6
    }

    const getEtcCols = () => {
        const headers_7:Record<string, string> = {}        
        headers_7['created_at'] = '거래수신 시간'
        headers_7['updated_at'] = '업데이트 시간'
        return headers_7
    }

    const headers0:any = getTransactionCols()
    const headers1:any = getMerchandiseCols()
    const headers2:any = getPGCols()
    const headers3:any = getFeeCols()
    const headers4:any = getPrivacyCols()
    const headers5:any = getPaymentCols()
    const headers6:any = getEtcCols()

    const headers: Record<string, string> = {
        ...headers0,
        ...headers1,
        ...headers2,
        ...headers3,
        ...headers4,
        ...headers5,
        ...headers6,
    }
    
    const sub_headers: any = []
    head.getSubHeaderCol('거래 정보', headers0, sub_headers)
    head.getSubHeaderCol('가맹점 정보', headers1, sub_headers)
    head.getSubHeaderCol('PG사 정보', headers2, sub_headers)
    head.getSubHeaderCol('수수료 정보', headers3, sub_headers)
    head.getSubHeaderCol('개인 정보', headers4, sub_headers)
    head.getSubHeaderCol('결제 정보', headers5, sub_headers)
    head.getSubHeaderCol('기타 정보', headers6, sub_headers)

    head.sub_headers.value = sub_headers
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
        head.exportToExcel(datas)        
    }
    return {
        store,
        head,
        exporter,
        printer,
        metas,
    }
})
