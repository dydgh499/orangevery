import { Header } from '@/views/headers'
import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { Searcher } from '@/views/searcher'
import type { DeductionHeader } from '@/views/types'
import { getUserLevel, salesLevels } from '@axios'
import corp from '@corp'

export const useSearchStore = defineStore('transSettleSalesSearchStore', () => {
    const store = Searcher('transactions/settle/salesforces')
    const head  = Header('transactions/settle/salesforces', '영업라인 정산관리')

    const all_sales = salesLevels()
    const all_cycles = settleCycles()
    const all_days = settleDays()
    const tax_types = settleTaxTypes()

    const settleObject = {
        'count' :  '매출건수',
        'amount' :  '금액',
        'total_trx_amount': '총 거래 수수료',
        'profit': '정산액',    
    }
    const headers1:Record<string, string | object> = {
        'id': 'NO.',
        'user_name' : '영업라인 ID',
        'sales_name': '상호',
        'level' : '등급',
        'settle_cycle' : '정산 주기',
        'settle_day' : '정산 요일',
        'settle_tax_type': '정산 세율',
        'last_settle_dt': '마지막 정산일',
        'total' : settleObject,
    }
    const headers2:DeductionHeader = {'deduction': {}}
    if(getUserLevel() >= 35)
        headers2['deduction']['input'] = '추가차감입력'
    headers2['deduction']['amount'] = '차감완료금'
    
    const headers3:Record<string, string | object> = {
        'terminal': {
            'settle_pay_module_idxs': '건수',
            'amount': '통신비',
            'under_sales_amount': '매출미달차감금',
        },
        'settle': {
            'amount': '정산금액',
            'deposit': '입금금액',
            'transfer': '이체금액',
        },
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
    head.sub_headers.value = [
        head.getSubHeaderFormat('영업라인 정보', 'id', 'last_settle_dt', 'string', 8),
        head.getSubHeaderFormat('매출', 'total', 'total', 'object', 4),
        head.getSubHeaderFormat('추가차감', 'deduction', 'deduction', 'object', Object.keys(headers2['deduction']).length),
        head.getSubHeaderFormat('장비', 'terminal', 'terminal', 'object', 2),
        head.getSubHeaderFormat('정산금', 'settle', 'settle', 'object', 3),
        head.getSubHeaderFormat('계좌정보', 'acct_bank_name', 'addr', 'string', 10),
        head.getSubHeaderFormat('승인', 'appr', 'appr', 'object', 4),
        head.getSubHeaderFormat('취소', 'cxl', 'cxl', 'object', 4),
    ]    
    const headers = {
        ...headers1,
        ...headers2,
        ...headers3,
    }

    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const exporter = async () => {      
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['level'] = all_sales.find(sales => sales.id === datas[i]['level'])?.title
            datas[i]['settle_cycle'] = all_cycles.find(sales => sales.id === datas[i]['settle_cycle'])?.title
            datas[i]['settle_day'] = all_days.find(sales => sales.id === datas[i]['settle_day'])?.title
            datas[i]['settle_tax_type'] = tax_types.find(sales => sales.id === datas[i]['settle_tax_type'])?.title

            datas[i]['appr.count'] =  datas[i]['appr']['count']
            datas[i]['appr.amount'] =  datas[i]['appr']['amount']
            datas[i]['appr.total_trx_amount'] =  datas[i]['appr']['total_trx_amount']
            datas[i]['appr.profit'] =  datas[i]['appr']['profit']

            datas[i]['cxl.count'] =  datas[i]['cxl']['count']
            datas[i]['cxl.amount'] =  datas[i]['cxl']['amount']
            datas[i]['cxl.total_trx_amount'] =  datas[i]['cxl']['total_trx_amount']
            datas[i]['cxl.profit'] =  datas[i]['cxl']['profit']

            datas[i]['total.count'] =  datas[i]['total']['count']
            datas[i]['total.amount'] =  datas[i]['total']['amount']
            datas[i]['total.total_trx_amount'] =  datas[i]['total']['total_trx_amount']
            datas[i]['total.profit'] =  datas[i]['total']['profit']

            datas[i]['terminal.amount'] = datas[i]['terminal']['amount']
            datas[i]['terminal.under_sales_amount'] = datas[i]['terminal']['under_sales_amount']
            datas[i]['terminal.settle_pay_module_idxs'] = datas[i]['terminal']['settle_pay_module_idxs'].length
        
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
        head.exportToExcel(datas)
    }

    return {
        store,
        head,
        exporter,
    }
})
