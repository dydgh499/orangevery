import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { DeductionHeader } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'

export const useSearchStore = defineStore('transSettlesMchtSearchStore', () => {    
    const store = Searcher('transactions/settle/merchandises')
    const head  = Header('transactions/settle/merchandises', '가맹점 정산관리')
    const headers1 = {
        'id': 'NO.',
        'user_name' : '가맹점 ID',
        'mcht_name' : '상호',
        'appr' : {
            'count' :  '매출건수',
            'amount' :  '금액',
            'trx_amount' :  '거래 수수료',
            'hold_amount': '유보금',
            'settle_fee' : '입금 수수료',
            'total_trx_amount': '총 거래 수수료',
            'profit': '정산액',
        },
        'cxl' : {
            'count' :  '매출건수',
            'amount' :  '금액',
            'trx_amount' : '거래 수수료',
            'hold_amount': '유보금',
            'settle_fee' :  '입금 수수료',
            'total_trx_amount': '총 거래 수수료',
            'profit': '정산액',
        },
        'count' :  '매출건수',
        'amount' :  '금액',
        'trx_amount' :  '거래 수수료',
        'hold_amount': '유보금',
        'settle_fee' :  '입금 수수료',
        'total_trx_amount': '총 거래 수수료',
        'profit': '정산액',
    }
    const headers2:DeductionHeader = {'deduction': {}}
    if(getUserLevel() >= 35)
        headers2['deduction']['input'] = '추가차감입력'
    headers2['deduction']['amount'] = '차감완료금'

    const settles:any = {}
    if(corp.pv_options.paid.use_cancel_deposit)
        settles['cancel_deposit'] = '취소입금합계'
    settles['amount'] = '정산금액'
    settles['deposit'] = '입금금액'
    settles['transfer'] = '이체금액'

    const headers3:Record<string, string | object> = {
        'terminal': {
            'amount': '통신비',
            'under_sales_amount': '매출미달차감금',
        },
        'settle': settles,
        'acct_bank_name': '은행',
        'acct_bank_code': '은행코드',
        'acct_name': '예금주',
        'acct_num': '계좌번호',
        'nick_name': '대표자명',
        'phone_num': '연락처',
        'resident_num': '주민등록번호',
        'business_num': '사업자등록번호',
        'sector': '업종',
        'addr': '주소',
    };
    if(getUserLevel() >= 35) {
        headers3['extra_col'] = '더보기'
    }
    
    head.main_headers.value = [
        '가맹점 정보',
        '승인',
        '취소',
        '매출',
        '추가차감',
        '장비',
        '정산금',
    ];
    const headers = {
        ...headers1,
        ...headers2,
        ...headers3,
    }
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()
    
    const exporter = async (type: number) => {     
        const keys = Object.keys(headers); 
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i <datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)        
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }

    return {
        store,
        head,
        exporter,
    }
})
