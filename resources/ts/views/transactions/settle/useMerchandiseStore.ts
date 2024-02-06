import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { DeductionHeader } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'

export const masking = ref(1)
export const useSearchStore = defineStore('transSettlesMchtSearchStore', () => {
    const store = Searcher('transactions/settle/merchandises')
    const head  = Header('transactions/settle/merchandises', '가맹점 정산관리')
    const settleObject = {
        'count' :  '건수',
        'amount' :  '금액',
        'trx_amount' :  '거래 수수료',
        'hold_amount': '유보금',
        'settle_fee' :  '입금 수수료',
        'total_trx_amount': '총 거래 수수료',
        'profit': '정산액',    
    }
    const headers1 = {
        'id': 'NO.',
        'user_name' : '가맹점 ID',
        'mcht_name' : '상호',
        'total': settleObject,
    }
    const headers2:DeductionHeader = {'deduction': {}}
    if(getUserLevel() >= 35)
        headers2['deduction']['input'] = '추가차감입력'
    headers2['deduction']['amount'] = '차감완료금'

    const settles:any = {}
    if(corp.pv_options.paid.use_cancel_deposit)
        settles['cancel_deposit_amount'] = '취소입금합계'
    if(corp.pv_options.paid.use_collect_withdraw)
        settles['collect_withdraw_amount'] = '모아서출금합계'
    if(corp.pv_options.paid.use_withdraw_fee)
        settles['withdraw_fee'] = '출금 수수료'

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
        'appr' : settleObject,
        'cxl' : settleObject,
    };
    if(getUserLevel() >= 35)
        headers3['extra_col'] = '더보기'

    head.sub_headers.value = [
        head.getSubHeaderFormat('가맹점 정보', 'id', 'mcht_name', 'string', 3),
        head.getSubHeaderFormat('매출', 'total', 'total', 'object', 7),
        head.getSubHeaderFormat('추가차감', 'deduction', 'deduction', 'object', Object.keys(headers2['deduction']).length),
        head.getSubHeaderFormat('장비', 'terminal', 'terminal', 'object', 2),
        head.getSubHeaderFormat('정산금', 'settle', 'settle', 'object', Object.keys(settles).length),
        head.getSubHeaderFormat('계좌정보', 'acct_bank_name', 'addr', 'string', 10),
        head.getSubHeaderFormat('승인', 'appr', 'appr', 'object', 7),
        head.getSubHeaderFormat('취소', 'cxl', 'cxl', 'object', 7),
    ]
    const headers = {
        ...headers1,
        ...headers2,
        ...headers3,
    }
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const exporter = async (type: number) => {     
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i <datas.length; i++) {
            datas[i]['appr.count'] =  datas[i]['appr']['count']
            datas[i]['appr.amount'] =  datas[i]['appr']['amount']
            datas[i]['appr.trx_amount'] =  datas[i]['appr']['trx_amount']
            datas[i]['appr.hold_amount'] =  datas[i]['appr']['hold_amount']
            datas[i]['appr.settle_fee'] =  datas[i]['appr']['settle_fee']
            datas[i]['appr.total_trx_amount'] =  datas[i]['appr']['total_trx_amount']
            datas[i]['appr.profit'] =  datas[i]['appr']['profit']

            datas[i]['cxl.count'] = datas[i]['cxl']['count']
            datas[i]['cxl.amount'] = datas[i]['cxl']['amount']
            datas[i]['cxl.trx_amount'] = datas[i]['cxl']['trx_amount']
            datas[i]['cxl.hold_amount'] = datas[i]['cxl']['hold_amount']
            datas[i]['cxl.settle_fee'] = datas[i]['cxl']['settle_fee']
            datas[i]['cxl.total_trx_amount'] = datas[i]['cxl']['total_trx_amount']
            datas[i]['cxl.profit'] =  datas[i]['cxl']['profit']

            datas[i]['total.count'] = datas[i]['total']['count']
            datas[i]['total.amount'] = datas[i]['total']['amount']
            datas[i]['total.trx_amount'] = datas[i]['total']['trx_amount']
            datas[i]['total.hold_amount'] = datas[i]['total']['hold_amount']
            datas[i]['total.settle_fee'] = datas[i]['total']['settle_fee']
            datas[i]['total.total_trx_amount'] = datas[i]['total']['total_trx_amount']
            datas[i]['total.profit'] =  datas[i]['total']['profit']

            datas[i]['terminal.amount'] = datas[i]['terminal']['amount']
            datas[i]['terminal.under_sales_amount'] =  datas[i]['terminal']['under_sales_amount']

            if(corp.pv_options.paid.use_cancel_deposit)
                datas[i]['settle.cancel_deposit_amount'] = datas[i]['settle']['cancel_deposit_amount']
            if(corp.pv_options.paid.use_collect_withdraw)
                datas[i]['settle.collect_withdraw_amount'] = datas[i]['settle']['collect_withdraw_amount']
            if(corp.pv_options.paid.use_withdraw_fee)
                datas[i]['settle.withdraw_fee'] = datas[i]['settle']['withdraw_fee']

            datas[i]['settle.amount'] = datas[i]['settle']['amount']
            datas[i]['settle.deposit'] = datas[i]['settle']['deposit']
            datas[i]['settle.transfer'] = datas[i]['settle']['transfer']
            datas[i]['deduction.amount'] =  datas[i]['deduction']['amount']
            datas[i]['deduction.input'] =  ''
            datas[i]['resident_num'] = datas[i]['resident_num_front'] + " - " + (masking.value ? "*******" : datas[i]['resident_num_back'])
            
            delete datas[i]['appr']
            delete datas[i]['total']
            delete datas[i]['cxl']
            delete datas[i]['terminal']
            delete datas[i]['settle']
            delete datas[i]['deduction']
            
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)        
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)        
    }

    return {
        store,
        head,
        exporter,
        masking,
    }
})
