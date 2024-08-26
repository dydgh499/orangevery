import { isFixplus } from '@/plugins/fixplus';
import { Header } from '@/views/headers';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { RealtimeHistory, Transaction } from '@/views/types';
import { getLevelByIndex, getUserLevel, user_info } from '@axios';
import { StatusColors } from '@core/enums';
import corp from '@corp';

export const getDateFormat = (_settle_dt: number) => {
    const settle_dt = _settle_dt.toString()
    return settle_dt.substr(0, 4) + '-' + settle_dt.substr(4, 2) + '-' + settle_dt.substr(6, 2)
}

export const realtimeDetailClass = (history: RealtimeHistory) => {
    if(history.result_code === '0000' && history.request_type === 6170)
        return 'text-success'
    else if(history.result_code !== '0000')
        return 'text-error'
    else
        return 'text-default'
}

export const realtimeResult = (item: Transaction) => {
    if(item.is_cancel)
        return StatusColors.Default
    //실시간 수수료 존재시(실시간 사용)    
    const is_success = item.realtimes?.find(obj => obj.result_code === '0000' && obj.request_type === 6170)
    const is_sending = item.realtimes?.find(obj => obj.result_code === '0050' && obj.request_type === 6170)
    const is_cancel = item.realtimes?.find(obj => obj.result_code === '-2')
    const is_error  = item.realtimes?.find(obj => obj.result_code !== '0000' && obj.result_code !== '0050')
    const is_deposit_cancel_job  = item.realtimes?.find(obj => obj.result_code === '-5')

    if(is_success)  //성공
        return StatusColors.Success
    if(is_sending)  // 처리중
        return StatusColors.Processing
    if(is_cancel)   // 취소
        return StatusColors.Cancel
    if(is_deposit_cancel_job)
        return StatusColors.DepositCancelJob
    if(item.use_realtime_deposit == 0) // 사용안함
        return StatusColors.Default
    if(is_error)    // 에러
        return StatusColors.Error

    if(item.fin_trx_delay as number < 0 && item.realtimes?.length == 0)    // 모아서 출금
        return StatusColors.Info
    if(item.realtimes?.length == 0) //요청 대기
    {
        const retry_able_time = (new Date(item.trx_dttm as string)).getTime() + (item.fin_trx_delay as number * 60000)
        const offset_time = new Date(retry_able_time) - new Date() 

        if(offset_time > 0) //요청 대기
            return StatusColors.Primary
        else //대기시간 초과
            return StatusColors.Timeout
    }
    
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

export const isRetryAble = (item: Transaction) => {
    const result = realtimeResult(item)
    if(result == StatusColors.Error || result == StatusColors.Timeout ||  result == StatusColors.DepositCancelJob)
        return true
    else
        return false
}

export const useSearchStore = defineStore('transSearchStore', () => {    
    const snackbar = <any>(inject('snackbar'))
    const store = Searcher('transactions')
    const head  = Header('transactions', '매출 관리')    
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

    const formatTime = <any>(inject('$formatTime'))
    const levels = corp.pv_options.auth.levels

    const getTransactionCols = () => {
        const headers_1:Record<string, string> = {}
        headers_1['id'] = 'NO.'
        if(isFixplus() === false) {
            headers_1['module_type'] = '거래 타입'
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
        if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
            headers_2['profit'] = '정산금'
            headers_2['settle_dt'] = '가맹점 정산예정일'
            headers_2['settle_id'] = '정산번호'
        }
        return headers_2
    }

    const getPGCols = () => {
        const headers_3:Record<string, string> = {}
        if(getUserLevel() >= 35) {
            headers_3['pg_id'] = 'PG사'
            headers_3['ps_id'] = '구간'
            headers_3['ps_fee'] = '구간 수수료'
            headers_3['mcht_settle_type'] = '가맹점 정산타입'
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
            headers_4['mcht_fee'] = '수수료'
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

        if(getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit)
            headers_7['realtime_result'] = '이체결과'
        
        headers_7['created_at'] = '노티생성 시간'
        headers_7['updated_at'] = '업데이트 시간'
        headers_7['extra_col'] = '더보기'
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
    ])
    if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
        metas.value.push({
            icon: 'ic-outline-payments',
            color: 'warning',
            title: '정산액 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        })
    }

    const exporter = async (type: number) => {      
        let count = metas.value[2]['subtitle'].replaceAll('건', '')
        if(Number(count.replaceAll(',', '')) > 100000) {
            snackbar.value.show('10만개 이상 다운로드 할 수 없습니다. 검색 폭을 줄여주세요.', 'warning')
            return
        }
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
            datas[i]['custom_id'] = cus_filters.find(cus => cus.id === datas[i]['custom_id'])?.name as string
            datas[i]['mcht_settle_type'] = settle_types.find(settle_type => settle_type.id === datas[i]['mcht_settle_type'])?.name as string
            datas[i]['resident_num'] = datas[i]['resident_num_front'] + "-" + (corp.pv_options.free.resident_num_masking ? "*******" : datas[i]['resident_num_back'])
            datas[i]['settle_id'] = settleIdCol(datas[i], store.params.level) === null ? '정산안함' : "#"+settleIdCol(datas[i], store.params.level)

            
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

            if(getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit)
                datas[i]['realtime_result'] = realtimeMessage(datas[i])
            
            datas[i]['mcht_fee'] = (datas[i]['mcht_fee'] * 100).toFixed(3)
            datas[i]['hold_fee'] = (datas[i]['hold_fee'] * 100).toFixed(3)
            datas[i]['ps_fee'] = (datas[i]['ps_fee'] * 100).toFixed(3)
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)        
    }
    
    const realtimeMessage = (item: Transaction):string => {
        const code = realtimeResult(item)
        if(code === StatusColors.Default)
            return 'N/A'
        else if(code === StatusColors.Primary) {
            const retry_able_time = (new Date(item.trx_dttm as string)).getTime() + (item.fin_trx_delay as number * 60000)
            return formatTime(new Date(retry_able_time))+'초 이체예정'
        }
        else if(code === StatusColors.Success)
            return '성공'
        else if(code === StatusColors.Processing)
            return '결과 처리중'
        else if(code === StatusColors.Info)
            return '모아서 출금예정'
        else if(code === StatusColors.DepositCancelJob)
            return '이체예약취소'
        else if(code === StatusColors.Error)
            return '실패'
        else if(code === StatusColors.Cancel)
            return '취소'
        else if(code === StatusColors.Timeout)
            return '이체예정시간 초과'
        else
            return '알수없는 상태'
    }

    return {
        store,
        head,
        exporter,
        metas,
        printer,
        realtimeMessage,
    }
})

export const defaultItemInfo = () => {
    const path = 'transactions'
    const item = reactive<Transaction>({
        id: 0,
        mcht_id: null,
        sales5_id: null,
        sales5_fee: 0,
        sales5_settle_id: null,
        sales4_id: null,
        sales4_fee: 0,
        sales4_settle_id: null,
        sales3_id: null,
        sales3_fee: 0,
        sales3_settle_id: null,
        sales2_id: null,
        sales2_fee: 0,
        sales2_settle_id: null,
        sales1_id: null,
        sales1_fee: 0,
        sales1_settle_id: null,
        sales0_id: null,
        sales0_fee: 0,
        sales0_settle_id: null,
        custom_id: null,
        mcht_fee: 0,
        hold_fee: 0,
        mcht_settle_id: null,
        mid: '',
        tid: '',
        module_type: null,
        pg_id: null,
        pmod_id: null,
        ps_id: null,
        terminal_id: null,
        ps_fee: 0,
        mcht_settle_type: null,
        mcht_settle_fee: 0,
        trx_dt: null,
        trx_tm: null,
        cxl_dt: null,
        cxl_tm: null,
        is_cancel: 0,
        amount: 0,
        ord_num: '',
        trx_id: '',
        ori_trx_id: '',
        card_num: '',
        installment: null,
        issuer: '',
        acquirer: '',
        appr_num: '',
        buyer_name: '',
        buyer_phone: '',
        item_name: '',
        dev_settle_type: corp.dev_settle_type,
        dev_realtime_fee: 0,
        dev_fee: (corp.dev_fee * 100).toFixed(3),
        mcht_settle_amount: 0,
        pg_settle_type: 1
    })  
    return {
        path, item
    }
}
