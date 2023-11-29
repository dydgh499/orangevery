import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import { StatusColors } from '@core/enums';

export const realtimeResult = (result_code: string) => {
    if(result_code == '0000')  //성공
        return StatusColors.Success
    else if(result_code == '0050')
        return StatusColors.Processing
    else
        return StatusColors.Error
}
export const realtimeMessage = (item: any) => {
    if(item.result_code == '0000')  //성공
        return '성공'
    else if(item.result_code == '0050')
        return '결과 처리중'
    else
        return item.message
}

export const useSearchStore = defineStore('merchandiseSelfSettleSearchStore', () => {
    const store = Searcher('transactions/settle/merchandises/collect-withdraws')
    const head  = Header('transactions/settle/merchandises/collect-withdraws', '모아서 출금 이력')
    const headers:Record<string, string> = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'result_code': '결과',
        'withdraw_amount': '출금금액',
        'withdraw_date': '출금일자',
        'acct_num': '계좌번호',
        'acct_name': '예금주',
        'acct_bank_name': '입금은행명',
        'acct_bank_code': '은행코드',
        'created_at': '생성시간',
    }

    head.main_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    
    const exporter = async (type: number) => {      
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        printer(type, r.data.content)
    }
    
    const printer = (type:number, datas: []) => {
        const keys = Object.keys(head.flat_headers.value)
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
