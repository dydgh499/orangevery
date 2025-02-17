

import { cxl_types, fin_trx_delays, installments, module_types } from '@/views/merchandises/pay-modules/useStore';
import { Merchandise, PayModule } from '@/views/types';
import { isEmpty } from '@core/utils';
import corp from '@corp';
import { useStore } from '../pay-gateways/useStore';

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

export const validateItems = (item: PayModule, i: number, mchts: Merchandise[]) => {
    const { pgs, pss, settle_types, terminals, finance_vans } = useStore()
    const date_regex = RegExp(/^\d{4}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/);

    item.mcht_name = item.mcht_name ? item.mcht_name?.trim() : ''
    const pg = pgs.find(a => a.id === item.pg_id)
    const ps = pss.find(a => a.id === item.ps_id)
    const settle_type = settle_types.find(a => a.id === item.settle_type)
    const module_type = module_types.find(a => a.id === item.module_type)
    const installment = installments.find(a => a.id === item.installment)
    const mcht = mchts.find(a => a.mcht_name == item.mcht_name)

    let finance_van = corp.pv_options.paid.use_realtime_deposit ? finance_vans.find(a => a.id === item.fin_id) : true
    let fin_trx_delay = corp.pv_options.paid.use_realtime_deposit ? fin_trx_delays.find(a => a.id === item.fin_trx_delay) : true
    let cxl_type = corp.pv_options.paid.use_realtime_deposit ? cxl_types.find(a => a.id === item.cxl_type) : true

    if (item.fin_id == null)
        finance_van = true
    if (item.fin_trx_delay == null)
        fin_trx_delay = true
    if (item.cxl_type == null)
        cxl_type = true

    if (mcht == null) 
        return [false, (i + 2) + '번째줄의 결제모듈의 가맹점 상호가 이상합니다.(' + item.mcht_name + ")"]
    else if (corp.pv_options.paid.use_pmid && item.p_mid == null) 
        return [false, (i + 2) + '번째줄의 PMID가 입력되지 않았습니다.']
    else if (pg === null || pg === undefined) 
        return [false, (i + 2) + '번째줄의 결제모듈의 PG사명이 이상합니다.']
    else if (ps === null || ps === undefined)
        return [false, (i + 2) + '번째줄의 결제모듈의 구간이 이상합니다.']
    else if (ps.pg_id != pg.id)
        return [false, (i + 2) + '번째줄의 결제모듈의 구간이 ' + pg.pg_name + '에 포함되는 구간이 아닙니다.']
    else if (isEmpty(item.note))
        return [false, (i + 2) + '번째줄의 결제모듈의 별칭은 필수로 입력해야합니다.']
    else if (isEmpty(item.mcht_name ?? ''))
        return [false, (i + 2) + '번째줄의 결제모듈의 가맹점 상호는 필수로 입력해야합니다.']
    else if (settle_type == null)
        return [false, (i + 2) + '번째줄의 결제모듈의 가맹점 정산타입이 이상합니다.']
    else if (module_type == null)
        return [false, (i + 2) + '번째줄의 결제모듈의 모듈타입이 이상합니다.']
    else if (installment == null) 
        return [false, (i + 2) + '번째줄의 결제모듈의 할부기간이 이상합니다.']
    else if (finance_van == null) 
        return [false, (i + 2) + '번째줄의 금융 VAN을 찾을 수 없습니다.']
    else if (fin_trx_delay == null) 
        return [false, (i + 2) + '번째줄의 이체 딜레이 타입을 찾을 수 없습니다.']
    else if (cxl_type == null) 
        return [false, (i + 2) + '번째줄의 취소 타입을 찾을 수 없습니다.']
    else if (item.contract_s_dt && date_regex.test(item.contract_s_dt) == false) 
        return [false, (i + 2) + '번째줄의 계약 시작일 포멧이 이상합니다.']
    else if (item.contract_e_dt && date_regex.test(item.contract_e_dt) == false) 
        return [false, (i + 2) + '번째줄의 계약 종료일 포멧이 이상합니다.']
    else if (item.begin_dt && date_regex.test(item.begin_dt) == false) 
        return [false, (i + 2) + '번째줄의 장비 개통일 포멧이 이상합니다.']
    else if (item.ship_out_dt && date_regex.test(item.ship_out_dt) == false)
        return [false, (i + 2) + '번째줄의 장비 출고일 포멧이 이상합니다.']
    else {
        item.mcht_id = mcht?.id || null
        return [true, '']
    }
}

export const useRegisterStore = defineStore('payModRegisterStore', () => {
    const getHeaders = () => {
        const headers1 = [
            {title: '가맹점 상호(O)', key: 'mcht_name'},
            {title: 'PG사명(O)', key: 'pg_id'},
            {title: '구간(O)', key: 'ps_id'},
            {title: '가맹점 정산타입(O)', key: 'settle_type'},
            {title: '입금 수수료(X)', key: 'settle_fee'},
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
            {title: '수기결제 여부(X)',  key: 'is_old_auth'},
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
        headers2.push({title: '결제금지타입(X)', key: 'pay_limit_type'})
        headers2.push(
            {title: '결제 연 한도(X)', key: 'pay_year_limit'},
            {title: '결제 월 한도(X)', key: 'pay_month_limit'},
            {title: '결제 일 한도(X)', key: 'pay_day_limit'},
            {title: '결제 단건 한도(X)', key: 'pay_single_limit'},
        )
        headers2.push(
            {title: '결제금지 시작시간(X)', key: 'pay_disable_s_tm'},
            {title: '결제금지 종료시간(X)', key: 'pay_disable_e_tm'},
        )
        if(corp.pv_options.paid.use_realtime_deposit) {
            headers2.push(
                {title: '실시간 사용여부(X)', key: 'use_realtime_deposit'},
                {title: '이체 모듈 타입(X)', key: 'fin_id'},
                {title: '이체 딜레이(X)', key: 'fin_trx_delay'},
                {title: '출금금지타입(X)', key: 'withdraw_limit_type'},
            )
        }
        return [...headers1, ...headers2]
    }
    
    const isPrimaryHeader = (key: string) => {
        const keys = [
            'pg_id', 'ps_id', 'settle_type', 'is_old_auth', 
            'comm_settle_type', 'cxl_type', 'module_type', 'terminal_id', 
            'comm_calc_level', 'under_sales_type', 'withdraw_limit_type'
        ]
        if(corp.pv_options.paid.use_realtime_deposit)
        {
            keys.push('fin_id')
            keys.push('fin_trx_delay')
        }
        return keys.includes(key)
    }
    const headers = getHeaders()

    return {
        headers, isPrimaryHeader
    }
})
