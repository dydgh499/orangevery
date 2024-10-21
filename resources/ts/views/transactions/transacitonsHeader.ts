import { getLevelByIndex, getUserLevel, user_info } from "@/plugins/axios"
import corp from "@/plugins/corp"
import { isFixplus } from "@/plugins/fixplus"
import { installments, module_types } from "../merchandises/pay-modules/useStore"
import { useStore } from "../services/pay-gateways/useStore"
import { Transaction } from "../types"
import { notiSendHistoryInterface, realtimeHistoryInterface } from "./transactions"

export const getProfitColName = (level: number) => {
    const levels = corp.pv_options.auth.levels
    if(level === 10)
        return '정산금'
    else if(level < 35)
        return levels[`sales${getLevelByIndex(level)}_name`] + ' 수익금'
    else if(level === 40)
        return '본사 수익금'
    else if(level === 50)
        return '개발사 수익금'
    else
        return ''
}

export const getDateFormat = (_settle_dt: number) => {
    if(_settle_dt) {
        const settle_dt = _settle_dt.toString()
        return settle_dt.substr(0, 4) + '-' + settle_dt.substr(4, 2) + '-' + settle_dt.substr(6, 2)    
    }
    else
        return ''
}

export const settleIdCol = (item: Transaction, search_level: number) => {
    if(search_level === 10)
        return Number(item['mcht_settle_id']) === 0 ? null : item['mcht_settle_id']
    else if(search_level < 35) {
        const dest_level = getLevelByIndex(search_level)
        return Number(item[`sales${dest_level}_settle_id`]) === 0 ? null : item[`sales${dest_level}_settle_id`]
    }
    else
        return null
}

export const transactionHeader = (table_name: string) => {
    const formatTime = <any>(inject('$formatTime'))
    const levels = corp.pv_options.auth.levels
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
    const { notiSendMessage } = notiSendHistoryInterface()
    const { realtimeMessage } = realtimeHistoryInterface(formatTime)
    
    const getTransactionCols = () => {
        const headers_1:Record<string, string> = {}
        headers_1['id'] = 'NO.'
        if(isFixplus() === false) {
            headers_1['module_type'] = '거래 타입'
            if(table_name === 'transactions')
                headers_1['note'] = '결제모듈 별칭'
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
            if(table_name !== 'sales-part') {
                headers_2['settle_dt'] = '가맹점 정산예정일'
                headers_2['settle_id'] = '정산번호'
            }
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
        headers_6['trx_id'] = '거래번호'    
        headers_6['ori_trx_id'] = '원거래번호'
        return headers_6
    }

    const getEtcCols = () => {
        const headers_7:Record<string, string> = {}
        if(table_name !== 'mcht-part' && table_name !== 'sales-part') {
            if(corp.pv_options.paid.use_noti)
                headers_7['noti_send_result'] = '노티전송결과'
            if(getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit)
                headers_7['realtime_result'] = '이체결과'    
        }
        
        headers_7['created_at'] = '거래수신 시간'
        headers_7['updated_at'] = '업데이트 시간'
        if(table_name !== 'mcht-part' && table_name !== 'sales-part') {
            headers_7['extra_col'] = '더보기'
        }
        return headers_7
    }

    const getChartCols = ()=> {
        const chart = [
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
        ]
        if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
            chart.push({
                icon: 'ic-outline-payments',
                color: 'warning',
                title: '정산액 합계',
                stats: '0',
                percentage: 0,
                subtitle: '0건',
            })
        }
        return chart
    }

    const dataToExcelFormat = (data: any, select_level: number) => {
        data['module_type'] = module_types.find(module_type => module_type['id'] === data['module_type'])?.title as string
        data['installment'] = installments.find(inst => inst['id'] === data['installment'])?.title as string
        data['pg_id'] = pgs.find(pg => pg['id'] === data['pg_id'])?.pg_name as string
        data['ps_id'] =  pss.find(ps => ps['id'] === data['ps_id'])?.name as string
        data['terminal_id'] = terminals.find(terminal => terminal['id'] === data['terminal_id'])?.name as string
        data['custom_id'] = cus_filters.find(cus => cus.id === data['custom_id'])?.name as string
        data['mcht_settle_type'] = settle_types.find(settle_type => settle_type.id === data['mcht_settle_type'])?.name as string
        data['resident_num'] = data['resident_num_front'] + "-" + (corp.pv_options.free.resident_num_masking ? "*******" : data['resident_num_back'])
        data['settle_id'] = settleIdCol(data, select_level) === null ? '정산안함' : "#"+settleIdCol(data, select_level)
        
        if(levels.sales5_use)
            data['sales5_fee'] = (data['sales5_fee'] * 100).toFixed(3)
        if(levels.sales4_use)
            data['sales4_fee'] = (data['sales4_fee'] * 100).toFixed(3)
        if(levels.sales3_use)
            data['sales3_fee'] = (data['sales3_fee'] * 100).toFixed(3)
        if(levels.sales2_use)
            data['sales2_fee'] = (data['sales2_fee'] * 100).toFixed(3)
        if(levels.sales1_use)
            data['sales1_fee'] = (data['sales1_fee'] * 100).toFixed(3)
        if(levels.sales0_use)
            data['sales0_fee'] = (data['sales0_fee'] * 100).toFixed(3)

        if(corp.pv_options.paid.use_noti)
            data['noti_send_result'] = notiSendMessage(data)
        if(getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit)
            data['realtime_result'] = realtimeMessage(data)
        
        data['mcht_fee'] = (data['mcht_fee'] * 100).toFixed(3)
        data['hold_fee'] = (data['hold_fee'] * 100).toFixed(3)
        data['ps_fee'] = (data['ps_fee'] * 100).toFixed(3)
        return data
    }

    const headers0:any = getTransactionCols()
    const headers1:any = getMerchandiseCols()
    const headers2:any = getPGCols()
    const headers3:any = getFeeCols()
    const headers4:any = getPrivacyCols()
    const headers5:any = getPaymentCols()
    const headers6:any = getEtcCols()
    const chart:any = getChartCols()

    return {
        headers0,
        headers1,
        headers2,
        headers3,
        headers4,
        headers5,
        headers6,
        chart,
        dataToExcelFormat
    }
}
