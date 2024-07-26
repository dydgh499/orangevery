import { Header } from '@/views/headers'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { Searcher } from '@/views/searcher'
import type { DeductionHeader } from '@/views/types'
import { getUserLevel, user_info } from '@axios'
import corp from '@corp'

export const useSearchStore = defineStore('transSettlesMchtSearchStore', () => {
    const setMerchandiseHeader = () => {
        const headers0:any = {}
        headers0['id'] = 'NO.'
        if(corp.pv_options.paid.use_settle_hold) {
            headers0['settle_hold_s_dt'] = '지급보류 시작일'
            headers0['settle_hold_reason'] = '지급보류 사유'
        }
        headers0['user_name'] = '가맹점 ID'
        headers0['mcht_name'] = '상호'
        if (levels.sales5_use && getUserLevel() >= 30) {
            headers0['sales5_id'] = levels.sales5_name
            headers0['sales5_fee'] = '수수료'
        }
        if (levels.sales4_use && getUserLevel() >= 25) {
            headers0['sales4_id'] = levels.sales4_name
            headers0['sales4_fee'] = '수수료'
        }
        if (levels.sales3_use && getUserLevel() >= 20) {
            headers0['sales3_id'] = levels.sales3_name
            headers0['sales3_fee'] = '수수료'
        }
        if (levels.sales2_use && getUserLevel() >= 17) {
            headers0['sales2_id'] = levels.sales2_name
            headers0['sales2_fee'] = '수수료'
        }
        if (levels.sales1_use && getUserLevel() >= 15) {
            headers0['sales1_id'] = levels.sales1_name
            headers0['sales1_fee'] = '수수료'
        }
        if (levels.sales0_use && getUserLevel() >= 13) {
            headers0['sales0_id'] = levels.sales0_name
            headers0['sales0_fee'] = '수수료'
        }
        return headers0    
    }

    const setDeductionHeader = () => {
        const headers2:DeductionHeader = {'deduction': {}}
        if(getUserLevel() >= 35)
            headers2['deduction']['input'] = '추가차감입력'
        headers2['deduction']['amount'] = '차감완료금'
        return headers2
    }

    const setSettleHeader = () => {
        const settles:any = {}
        settles['cancel_deposit_amount'] = '취소입금합계'
        if(corp.pv_options.paid.use_withdraw_fee)
            settles['withdraw_fee'] = '출금 수수료'
    
        settles['amount'] = '정산금액'
        settles['deposit'] = '입금금액'
        settles['transfer'] = '이체금액'
    
        const headers3:Record<string, string | object> = {
            'terminal': {
                'settle_pay_module_idxs': '건수',
                'amount': '통신비',
                'under_sales_amount': '매출미달차감금',
            },
            'settle': settles,
        };
        if(is_show_acct) {
            headers3['acct_bank_name'] = '은행'
            headers3['acct_bank_code'] = '은행코드'
            headers3['acct_name'] = '예금주'
            headers3['acct_num'] = '계좌번호'    
        }
    
        headers3['nick_name'] = '대표자명'
        headers3['phone_num'] = '연락처'
        headers3['resident_num'] = '주민등록번호'
        headers3['business_num'] = '사업자등록번호'
        headers3['sector'] = '업종'
        headers3['addr'] = '주소'
        headers3['appr'] = settleObject
        headers3['cxl'] = settleObject
    
        if(getUserLevel() >= 35)
            headers3['extra_col'] = '더보기'
        return [settles, headers3]
    }

    const store = Searcher('transactions/settle/merchandises')
    const head  = Header('transactions/settle/merchandises', '가맹점 정산관리')
    const { findSalesName } = useSalesFilterStore()
    const levels = corp.pv_options.auth.levels

    const is_show_acct = ((getUserLevel() == 10 && !user_info.value.is_hide_account) || getUserLevel() >= 13) ? true : false
    const settleObject = {
        'count' :  '건수',
        'amount' :  '금액',
        'trx_amount' :  '거래 수수료',
        'hold_amount': '유보금',
        'settle_fee' :  '입금 수수료',
        'total_trx_amount': '총 거래 수수료',
        'profit': '정산액',    
    }
    const headers0:any = setMerchandiseHeader()
    const headers1:any = {
        'total': settleObject
    }
    const headers2:any = setDeductionHeader()
    const [settles, headers3] = setSettleHeader()

    const header0_keys = Object.keys(headers0)
    head.sub_headers.value = [
        head.getSubHeaderFormat('가맹점 정보', 'id', header0_keys[header0_keys.length - 1], 'string', header0_keys.length),
        head.getSubHeaderFormat('매출', 'total', 'total', 'object', 7),
        head.getSubHeaderFormat('추가차감', 'deduction', 'deduction', 'object', Object.keys(headers2['deduction']).length),
        head.getSubHeaderFormat('장비', 'terminal', 'terminal', 'object', 3),
        head.getSubHeaderFormat('정산금', 'settle', 'settle', 'object', Object.keys(settles).length),
        head.getSubHeaderFormat(is_show_acct ? '계좌정보' : '개인정보', is_show_acct ? 'acct_bank_name' : 'nick_name', 'addr', 'string', is_show_acct ? 10 : 6),
        head.getSubHeaderFormat('승인', 'appr', 'appr', 'object', 7),
        head.getSubHeaderFormat('취소', 'cxl', 'cxl', 'object', 7),
    ]
    const headers = {
        ...headers0,
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
            if (levels.sales5_use && getUserLevel() >= 30) {
                datas[i]['sales5_id'] = findSalesName('sales5_id', datas[i]['sales5_id'])
                datas[i]['sales5_fee'] = (datas[i]['sales5_fee'] * 100).toFixed(3)
            }
            if (levels.sales4_use && getUserLevel() >= 25) {
                datas[i]['sales4_id'] = findSalesName('sales4_id', datas[i]['sales4_id'])
                datas[i]['sales4_fee'] = (datas[i]['sales4_fee'] * 100).toFixed(3)
            }
            if (levels.sales3_use && getUserLevel() >= 20) {
                datas[i]['sales3_id'] = findSalesName('sales3_id', datas[i]['sales3_id'])
                datas[i]['sales3_fee'] = (datas[i]['sales3_fee'] * 100).toFixed(3)
            }
            if (levels.sales2_use && getUserLevel() >= 17) {
                datas[i]['sales2_id'] = findSalesName('sales2_id', datas[i]['sales2_id'])
                datas[i]['sales2_fee'] = (datas[i]['sales2_fee'] * 100).toFixed(3)
            }
            if (levels.sales1_use && getUserLevel() >= 15) {
                datas[i]['sales1_id'] = findSalesName('sales1_id', datas[i]['sales1_id'])
                datas[i]['sales1_fee'] = (datas[i]['sales1_fee'] * 100).toFixed(3)
            }
            if (levels.sales0_use && getUserLevel() >= 13) {
                datas[i]['sales5_id'] = findSalesName('sales0_id', datas[i]['sales0_id'])
                datas[i]['sales0_fee'] = (datas[i]['sales0_fee'] * 100).toFixed(3)
            }
            
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

            datas[i]['terminal.settle_pay_module_idxs'] = datas[i]['terminal']['settle_pay_module_idxs'].length
            datas[i]['terminal.amount'] = datas[i]['terminal']['amount']
            datas[i]['terminal.under_sales_amount'] = datas[i]['terminal']['under_sales_amount']

            datas[i]['settle.cancel_deposit_amount'] = datas[i]['settle']['cancel_deposit_amount']
            if(corp.pv_options.paid.use_withdraw_fee)
                datas[i]['settle.withdraw_fee'] = datas[i]['settle']['withdraw_fee']

            datas[i]['settle.amount'] = datas[i]['settle']['amount']
            datas[i]['settle.deposit'] = datas[i]['settle']['deposit']
            datas[i]['settle.transfer'] = datas[i]['settle']['transfer']
            datas[i]['deduction.amount'] =  datas[i]['deduction']['amount']
            datas[i]['deduction.input'] =  ''
            datas[i]['resident_num'] = datas[i]['resident_num_front'] + "-" + (corp.pv_options.free.resident_num_masking ? "*******" : datas[i]['resident_num_back'])
            
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
    }
})
