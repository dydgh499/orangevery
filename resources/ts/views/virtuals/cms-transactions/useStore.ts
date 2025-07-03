
import { StatusColors } from '@/@core/enums'
import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import { useRequestStore } from '@/views/request';
import { useStore } from '@/views/services/options/useStore'
import { CmsTransactionHistory, Options } from '@/views/types'

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
export const withdraw_status = <Options[]>([
    {id:0, title:'대기'},
    {id:1, title:'완료'},
    {id:2, title:'실패'},
])

export const withdrawInterface = () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const { post } = useRequestStore()

    const getSuccessResultId = (histories: CmsTransactionHistory[]) => {
        const realtime = histories.find(obj => obj.result_code === '0000')
        return realtime ? realtime.id : 0
    }

    const cancelJobs = async (ids: any) => {
        if (await alert.value.show('정말 해당건의 출금예약을 취소처리 하시겠습니까?')) {
            const res = await post('/api/v1/manager/virtuals/cms-transactions/cancel-job', {
                ids: ids
            }, true)
            snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
        }
    }

    return {
        getSuccessResultId,
        cancelJobs,
    }
}

export const useSearchStore = defineStore('useCMSTransactionSearchStore', () => {
    const store = Searcher('virtuals/cms-transactions')
    const head  = Header('virtuals/cms-transactions', '가상계좌 입출금')
    const { finance_vans } = useStore()
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'amount': '거래금액',
        'acct_num' : '계좌번호',
        'acct_name' : '계좌명',
        'acct_bank_name' : '은행명',
        'acct_bank_code' : '은행코드',
        'withdraw_book_time' : '예약시간',
        'withdraw_status' : '이체상태',
        'extra_cols' : '더보기',
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['fin_id'] = (finance_vans.find(obj => obj.id == datas[i]['fin_id']))?.nick_name
            datas[i]['result_code'] = realtimeMessage(datas[i])
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }

    const metas = ref([])
    
    return {
        store,
        head,
        exporter,
        metas,
    }
})

