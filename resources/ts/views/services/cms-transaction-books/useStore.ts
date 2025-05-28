import { StatusColors, transactionColors } from '@/@core/enums'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'

export const realtimeResult = (result_code: string) => {
    if(result_code === '0000')  //성공
        return StatusColors.Success
    else if(result_code === '0050')
        return StatusColors.Processing
    else
        return StatusColors.Error
}
export const realtimeMessage = (item: any) => {
    if(item.result_code === '0000')  //성공
        return '성공'
    else if(item.result_code === '0050')
        return '결과 처리중'
    else
        return item.message
}

export const withdrawStatusCode = (history: VirtualAccountHistory) => {
    if (history.withdraw_status === 0) {
        const trans_at = (new Date(history.withdraw_schedule_time as string)).getTime() + 30000;
        const offset_at = trans_at - new Date().getTime();
        return offset_at < 0 ? transactionColors.Timeout : transactionColors.Book;
    }

    const statusMap: Record<number, number> = {
        1: transactionColors.Success,
        2: transactionColors.Error,
        3: transactionColors.Cancel,
        4: transactionColors.BookCancel,
    };

    return statusMap[history.withdraw_status] ?? transactionColors.NA;
}

export const withdrawInterface = () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const { post } = useRequestStore()

    const cancelJobs = async (id: string[]) => {
        if (await alert.value.show('정말 해당건의 출금예약을 취소처리 하시겠습니까?')) {
            const res = await post('/api/v1/manager/cms-transaction-books/cancel-job', {
                id: id
            }, true)
            snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
        }
    }

    const withdrawRetry = async (id: number) => {
        if (await alert.value.show('정말 해당건을 재출금시도 하시겠습니까?')) {
            const res = await post('/api/v1/manager/virtual-accounts/histories/retry-withdraw', {
                id: id,
            }, false)
            snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
        }
        else
            return null
    }

    return {
        withdrawRetry,
        cancelJobs,
    }
}

export const useSearchStore = defineStore('useCMSTransactionBookSearchStore', () => {
    const store = Searcher('services/cms-transaction-books')
    const head  = Header('services/cms-transaction-books', '가상계좌 출금예약 관리')
    const { finance_vans } = useStore()
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'result_code': '성공여부',
        'fin_id': '거래모듈',
        'is_withdraw': '거래타입', //출금, 입금
        'amount': '거래금액',
        'acct_num' : '계좌번호',
        'acct_name' : '예금주명',
        'acct_bank_name' : '은행명',
        'acct_bank_code' : '은행코드',
        'withdraw_book_time': '이체예정시간',
        'withdraw_status': '예약상태',
        'note': '메모사항',
        'created_at': '생성시간',
        'extra_col': '삭제하기',
    }

    head.sub_headers.value = [
        head.getSubHeaderFormat('거래정보', 'id', 'amount', 'string', 5),
        head.getSubHeaderFormat('출금정보', 'acct_num', 'acct_bank_code', 'string', 4),
        head.getSubHeaderFormat('기타', 'withdraw_book_time', 'extra_col', 'string', 5),
    ]
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['fin_id'] = (finance_vans.find(obj => obj.id === datas[i]['fin_id']))?.nick_name
            datas[i]['is_withdraw'] = datas[i]['is_withdraw'] ? '출금' : '입금'
            datas[i]['result_code'] = realtimeMessage(datas[i])
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