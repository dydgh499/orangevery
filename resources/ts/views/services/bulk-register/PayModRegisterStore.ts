

import { Header } from '@/views/headers';
import corp from '@corp';

export const useRegisterStore = defineStore('payModRegisterStore', () => {
    const head  = Header('salesforces', '결제모듈 대량등록 포멧')
    const headers: Record<string, string> = {
        'mcht_name': '가맹점 상호(O)',
        'pg_id': 'PG사명(O)',
        'ps_id': '구간(O)',
        'settle_type': '가맹점 정산일(O)',
        'settle_fee': '정산 수수료(O)',
        'terminal_id': '단말기 종류(X)',
        'module_type': '결제모듈 타입(O)',
        'api_key': 'API KEY(X)',
        'sub_key': 'SUB KEY(X)',
        'mid': 'MID(X)',
        'tid': 'TID(X)',
        'serial_num': '시리얼 번호(X)',
        'comm_settle_fee': '통신비 입금 수수료(X)',
        'comm_settle_type': '통신비 정산일(X)',
        'comm_calc_level': '통신비 정산 주체(X)',
        'under_sales_amt': '매출미달 차감금(X)',
        'begin_dt': '단말기 개통일(X)',
        'ship_out_dt': '단말기 출고일(X)',
        'ship_out_stat': '단말기 출고상태(X)',
        'is_old_auth': '수기결제 여부(O)',
        'installment': '할부 한도(O)',
        'show_easy_view': '간편보기 결제창 노출(X)',
        'abnormal_trans_limit': '이상거래 한도(X)',
        'note': '별칭(O)',
    };
    if(corp.pv_options.paid.use_dup_pay_validation) {
        headers['pay_dupe_limit'] = '중복결제 허용회수(X)'
    }
    if(corp.pv_options.paid.use_pay_limit) {
        headers['pay_year_limit'] = '결제 연 한도(X)'
        headers['pay_month_limit'] = '결제 월 한도(X)'
        headers['pay_day_limit'] = '결제 일 한도(X)'
    }
    if(corp.pv_options.paid.use_forb_pay_time) {
        headers['pay_disable_s_tm'] = '결제금지 시작시간(X)'
        headers['pay_disable_e_tm'] = '결제금지 종료시간(X)'
    }
    
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    return {
        head, headers
    }
})
