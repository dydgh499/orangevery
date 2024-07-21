

import { Header } from '@/views/headers';
import corp from '@corp';

export const useRegisterStore = defineStore('payModRegisterStore', () => {
    const head  = Header('pay-modules/bulk-register', '결제모듈 대량등록 포멧')
    const headers1: Record<string, string> = {
        'mcht_name': '가맹점 상호(O)',
        'pg_id': 'PG사명(O)',
        'ps_id': '구간(O)',
        'settle_type': '가맹점 정산타입(O)',
        'settle_fee': '입금 수수료(O)',
        'contract_s_dt': '계약 시작일(X)',
        'contract_e_dt': '계약 종료일(X)',
    }
    const headers2: Record<string, string> = {
        'terminal_id': '장비 종류(X)',
        'module_type': '결제모듈 타입(O)',
        'api_key': 'API KEY(X)',
        'sub_key': 'SUB KEY(X)',
    }
    if(corp.pv_options.paid.use_pmid) {
        headers2['p_mid'] = 'PMID(O)'
    }
    Object.assign(headers2, {
        'mid': 'MID(X)',
        'tid': 'TID(X)',
        'serial_num': '시리얼 번호(X)',
        'comm_settle_fee': '통신비(X)',
        'comm_settle_type': '통신비 정산타입(X)',
        'under_sales_amt': '매출미달 차감금(X)',
        'under_sales_limit':'매출미달 하한금(X)',
        'under_sales_type':'매출미달 적용기간(X)',
        'comm_settle_day': '정산일(X)',
        'comm_calc_level': '정산주체(X)',
        'begin_dt': '개통일(X)',
        'ship_out_dt': '출고일(X)',
        'ship_out_stat': '출고상태(X)',
        'is_old_auth': '수기결제 여부(O)',
        'installment': '할부 한도(O)',
        'show_pay_view': '결제창 노출여부(X)',
        'abnormal_trans_limit': '이상거래 한도(X)',
        'cxl_type': '취소 타입(X)',
        'pay_dupe_least': '중복거래 하한금(X)',
        'note': '별칭(O)',
    })
    
    if(corp.pv_options.paid.use_dup_pay_validation) {
        headers2['pay_dupe_limit'] = '동일카드 결제허용 회수(X)'
    }
    if(corp.pv_options.paid.use_pay_limit) {
        headers2['pay_year_limit'] = '결제 연 한도(X)'
        headers2['pay_month_limit'] = '결제 월 한도(X)'
        headers2['pay_day_limit'] = '결제 일 한도(X)'
        headers2['pay_single_limit'] = '결제 단건 한도(X)'
    }
    if(corp.pv_options.paid.use_forb_pay_time) {
        headers2['pay_disable_s_tm'] = '결제금지 시작시간(X)'
        headers2['pay_disable_e_tm'] = '결제금지 종료시간(X)'
    }
    if(corp.pv_options.paid.use_realtime_deposit)
    {
        headers2['use_realtime_deposit'] = '실시간 사용여부(X)'
        headers2['fin_id'] = '이체 모듈 타입(X)'
        headers2['fin_trx_delay'] = '이체 딜레이(X)'
    }
    
    const headers: Record<string, string> = {
        ...headers1,
        ...headers2
    }
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const isPrimaryHeader = (key: string) => {
        const keys = ['pg_id', 'ps_id', 'is_old_auth', 'comm_settle_type', 'cxl_type', 'module_type', 'terminal_id', 'comm_calc_level', 'under_sales_type']
        if(corp.pv_options.paid.use_realtime_deposit)
        {
            keys.push('fin_id')
            keys.push('fin_trx_delay')
        }
        return keys.includes(key)
    }

    return {
        head, headers, isPrimaryHeader
    }
})
