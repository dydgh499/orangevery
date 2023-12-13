import router from '@/router';
import { Header } from '@/views/headers';
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { useRequestStore } from '@/views/request';
import { Searcher } from '@/views/searcher';
import { useStore } from '@/views/services/pay-gateways/useStore';
import type { RealtimeHistory, Transaction } from '@/views/types';
import { getUserLevel, user_info } from '@axios';
import { StatusColors } from '@core/enums';
import corp from '@corp';
import * as XLSX from 'xlsx';

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
    const is_cancel = item.realtimes?.find(obj => obj.request_type === -2)
    const is_error  = item.realtimes?.find(obj => obj.result_code !== '0000' && obj.result_code !== '0050')
    if(is_success)  //성공
        return StatusColors.Success
    if(is_sending)  // 처리중
        return StatusColors.Processing
    if(is_cancel)   // 취소
        return StatusColors.Cancel
    if(item.use_realtime_deposit == 0) // 사용안함
        return StatusColors.Default
    if(is_error)    // 에러
        return StatusColors.Error

    if(item.fin_trx_delay == -1 && item.realtimes?.length == 0)    // 모아서 출금
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

export const isRetryAble = (item: Transaction) => {
    const result = realtimeResult(item)
    if(result == StatusColors.Error || result == StatusColors.Timeout)
        return true
    else
        return false
}

export const useSearchStore = defineStore('transSearchStore', () => {    
    const store = Searcher('transactions')
    const head  = Header('transactions', '매출 관리')    
    const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
    const { get } = useRequestStore()

    const formatTime = <any>(inject('$formatTime'))
    const levels = corp.pv_options.auth.levels
    const headers: Record<string, string> = {
        'id': 'NO.',
        'module_type': '거래 타입',
        'note': '결제모듈 별칭',
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
    headers['user_name'] = '가맹점 ID'

    if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13)
    {
        headers['mcht_fee'] = '수수료'
        headers['hold_fee'] = '유보금 수수료'
    }
    headers['resident_num'] = '주민등록번호'
    headers['business_num'] = '사업자등록번호'
    headers['nick_name'] = '대표자명'
    
    if(getUserLevel() >= 35)
    {
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
    if(getUserLevel() >= 13)
    {
        headers['ord_num'] = '주문번호'
        headers['trx_id'] = '거래번호'
        headers['ori_trx_id'] = '원거래번호'
    }
    
    if(getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit)
        headers['realtime_result'] = '이체결과'
    
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'
    headers['extra_col'] = '더보기'
    
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
            datas[i]['custom_id'] = cus_filters.find(cus => cus.id === datas[i]['custom_id'])?.name as string
            datas[i]['mcht_settle_type'] = settle_types.find(settle_type => settle_type.id === datas[i]['mcht_settle_type'])?.name as string
            
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
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }

    const mchtGroup = async() => {        
        const url = '/api/v1/manager/transactions/merchandises/groups'
        const r = await get(url, {params: router.currentRoute.value.query})
        const datas = []
        datas.push([
                'NO.',
                '가맹점 상호',
                '주민등록번호',
               '사업자등록번호',
                '대표자명',
                '주소',
                '업종',
                '결제건수',
                '승인금액',
                '취소금액',
                '거래금액',
                '거래 수수료',
                '거래 수수료 공급가액',
                '거래 수수료 세액',
                '정산금액',
                '커스텀 필터',
        ])
        for (let i = 0; i < r.data.length; i++) 
        {
            r.data[i]['cxl_amount'] = Number(r.data[i]['cxl_amount'])
            r.data[i]['appr_amount'] = Number(r.data[i]['appr_amount'])
            r.data[i]['profit'] = Number(r.data[i]['profit'])
            r.data[i]['count'] = Number(r.data[i]['appr_count']) + Number(r.data[i]['cxl_count'])
            r.data[i]['total_amount'] = r.data[i]['appr_amount'] + r.data[i]['cxl_amount']
            r.data[i]['trx_amount'] = r.data[i]['total_amount'] - r.data[i]['profit']
            r.data[i]['trx_supply_amount'] = Math.round(r.data[i]['trx_amount']/1.1)
            r.data[i]['trx_tax_amount'] = r.data[i]['trx_amount'] - r.data[i]['trx_supply_amount']
            
            datas.push([
                r.data[i]['id'],
                r.data[i]['mcht_name'],
                r.data[i]['resident_num'],
                r.data[i]['business_num'],
                r.data[i]['nick_name'],
                r.data[i]['addr'],
                r.data[i]['sector'],
                r.data[i]['count'],
                r.data[i]['appr_amount'],
                r.data[i]['cxl_amount'],
                r.data[i]['total_amount'],
                r.data[i]['trx_amount'],
                r.data[i]['trx_supply_amount'],
                r.data[i]['trx_tax_amount'],
                r.data[i]['profit'],
                cus_filters.find(cus => cus.id === r.data[i]['custom_id'])?.name,
            ])
        }

        const date = new Date().toISOString().split('T')[0];
        const ws: XLSX.WorkSheet = XLSX.utils.aoa_to_sheet(datas)
        const wb = XLSX.utils.book_new()
        XLSX.utils.book_append_sheet(wb, ws, "가맹점별 매출집계")
        XLSX.writeFile(wb, "가맹점별 매출집계_" + date + ".xlsx")
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
        mchtGroup,
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
        is_cancel: false,
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
        dev_fee: (corp.dev_fee * 100).toFixed(3)
    })  
    return {
        path, item
    }
}
