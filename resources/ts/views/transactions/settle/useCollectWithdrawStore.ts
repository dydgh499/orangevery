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

export const useSearchStore = defineStore('collectWithdrawStore', () => {
    const store = Searcher('transactions/settle/collect-withdraws')
    const head  = Header('transactions/settle/collect-withdraws', '모아서 출금 관리')
    const headers:Record<string, string> = {
        'id': 'NO.',
        'mcht_name': '가맹점 상호',
        'total_amount': '총 매출액',
        'settle_amount': '총 정산액',
        'total_withdraw_amount': '총 출금액',
        'cancel_deposit': '취소수기입금 합계',
        'collect_withdraw_fee': '출금 수수료',
        'withdraw_able_amount': '출금 가능금액',
    }

    head.sub_headers.value = []
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
        head.exportToExcel(datas)        
    }
    return {
        store,
        head,
        exporter,
    }
})
