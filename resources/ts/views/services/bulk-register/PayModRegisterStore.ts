

import { Header } from '@/views/headers';
import corp from '@corp';


export const keyCreater = (snackbar: any, items: any) => {
    const getRandomNumber = (min: number, max: number) => {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
    const generateRandomString = (id: number) => {
        const remaining_length = 64 - id.toString().length
        const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < remaining_length; i++) {
            const rand_idx = Math.floor(Math.random() * characters.length);
            result += characters.charAt(rand_idx);
        }
        return id + result;
    }
    
    const payKeyCreater = () => {
        snackbar.value.show('PAY KEY들을 자동 발급 중입니다.', 'primary')
        for (let i = 0; i < items.value.length; i++) {
            items.value[i].pay_key = generateRandomString(getRandomNumber(1, 999999))        
        }
        snackbar.value.show('PAY KEY들이 발급 되었습니다.', 'success')
    }

    const signKeyCreater = () => {
        snackbar.value.show('서명 KEY들을 자동 발급 중입니다.', 'primary')
        for (let i = 0; i < items.value.length; i++) {
            items.value[i].sign_key = generateRandomString(getRandomNumber(1, 999999))        
        }
        snackbar.value.show('서명 KEY들이 발급 되었습니다.', 'success')
    }

    return {
        payKeyCreater,
        signKeyCreater
    }
}

const getHeaders = () => {
    const headers1 = [
        {title: '가맹점 상호(O)', key: 'mcht_name'},
        {title: 'PG사명(O)', key: 'pg_id'},
        {title: '구간(O)', key: 'ps_id'},
        {title: '가맹점 정산타입(O)', key: 'settle_type'},
        {title: '입금 수수료(O)', key: 'settle_fee'},
        {title: '계약 시작일(X)', key: 'contract_s_dt'},
        {title: '계약 종료일(X)', key: 'contract_e_dt'},
    ]
    const headers2 = [
        {title: '장비 종류(X)', key: 'terminal_id'},
        {title: '결제모듈 타입(O)', key: 'module_type'},
        {title: 'API KEY(X)', key: 'api_key'},
        {title: 'SUB KEY(X)', key: 'sub_key'},
    ]
    if(corp.pv_options.paid.use_pmid) {
        headers2.push({title: 'PMID(O)', key: 'p_mid'})
    }
    
    headers2.push(
        {title: 'MID(X)',  key: 'mid'},
        {title: 'TID(X)',  key: 'tid'},
        {title: '시리얼 번호(X)',  key: 'serial_num'},
        {title: '통신비(X)',  key: 'comm_settle_fee'},
        {title: '통신비 정산타입(X)',  key: 'comm_settle_type'},
        {title: '매출미달 차감금(X)',  key: 'under_sales_amt'},
        {title: '매출미달 하한금(X)',  key: 'under_sales_limit'},
        {title: '매출미달 적용기간(X)',  key: 'under_sales_type'},
        {title: '정산일(X)',  key: 'comm_settle_day'},
        {title: '정산주체(X)',  key: 'comm_calc_level'},
        {title: '개통일(X)',  key: 'begin_dt'},
        {title: '출고일(X)',  key: 'ship_out_dt'},
        {title: '출고상태(X)',  key: 'ship_out_stat'},
        {title: '수기결제 여부(O)',  key: 'is_old_auth'},
        {title: '할부 한도(O)',  key: 'installment'},
        {title: '결제창 보안등급(X)',  key: 'pay_window_secure_level'},
        {title: '이상거래 한도(X)',  key: 'abnormal_trans_limit'},
        {title: '취소 타입(X)',  key: 'cxl_type'},
        {title: '중복거래 하한금(X)',  key: 'pay_dupe_least'},
        {title: '별칭(O)',  key: 'note'},
    )
    
    if(corp.pv_options.paid.use_dup_pay_validation) {
        headers2.push(
            {title: '동일카드 결제허용 회수(X)', key: 'pay_dupe_limit'}
        )
    }
    if(corp.pv_options.paid.use_pay_limit) {
        headers2.push(
            {title: '결제 연 한도(X)', key: 'pay_year_limit'},
            {title: '결제 월 한도(X)', key: 'pay_month_limit'},
            {title: '결제 일 한도(X)', key: 'pay_day_limit'},
            {title: '결제 단건 한도(X)', key: 'pay_single_limit'},
        )
    }
    if(corp.pv_options.paid.use_forb_pay_time) {
        headers2.push(
            {title: '결제금지 시작시간(X)', key: 'pay_disable_s_tm'},
            {title: '결제금지 종료시간(X)', key: 'pay_disable_e_tm'},
            {title: '결제 일 한도(X)', key: 'pay_day_limit'},
            {title: '결제 단건 한도(X)', key: 'pay_single_limit'},
        )
    }
    if(corp.pv_options.paid.use_realtime_deposit) {
        headers2.push(
            {title: '실시간 사용여부(X)', key: 'use_realtime_deposit'},
            {title: '이체 모듈 타입(X)', key: 'fin_id'},
            {title: '이체 딜레이(X)', key: 'fin_trx_delay'},
        )
    }
    return [...headers1, ...headers2]
}

export const headers = getHeaders()

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
        'pay_window_secure_level': '결제창 보안등급(X)',
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
