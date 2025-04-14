import { Header } from '@/views/headers';
import { useRequestStore } from '@/views/request';
import { Searcher } from '@/views/searcher';
import { Transaction, VirtualAccountWithdraw } from '@/views/types';
import { withdraw_types } from '@/views/virtual-accounts/wallets/useStore';

export const transTypeColors = (type: number) => {
    return type ? 'primary' : 'warning'
}

export const transTypeNames = (type: number) => {
    return type ? '출금' : '입금'
}

export const depositStatusNames = (type: number) => {
    if(type === 0)
        return '대기'
    else if(type === 1)
        return '성공'
    else if(type === 2)
        return '실패'
    else
        return '알수없는코드'
}

export const depositStatusColors = (type: number) => {
    if(type === 0)
        return 'default'
    else if(type === 1)
        return 'success'
    else if(type === 2)
        return 'error'
    else
        return 'default'
}

export const withdrawStatusNames = (type: number) => {
    if(type === 0)
        return '대기'
    else if(type === 1)
        return '성공'
    else if(type === 2)
        return '실패'
    else if(type === 3)
        return '승인취소'
    else if(type === 4)
        return '예약취소'
    else
        return '알수없는코드'
}

export const withdrawStatusColors = (type: number) => {
    if(type === 0)
        return 'default'
    else if(type === 1)
        return 'success'
    else if(type === 2)
        return 'error'
    else if(type === 3)
        return 'info'
    else if(type === 4)
        return 'warning'
    else
        return 'default'
}

export const withdrawInterface = () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const { post } = useRequestStore()

    const isReSettleAble = (transaction: Transaction) => {
        if(transaction.use_realtime_deposit && transaction.va_id) {
            if(transaction.mcht_settle_id === null)
                return true
        }
        return false
    }

    const isCollectWithdraw = (transaction: Transaction) => {
        if(transaction.use_realtime_deposit && transaction.va_id) {
            if(transaction.mcht_settle_id)
                return true
        }
        return false
    }

    const withdrawStatusColors = (history: VirtualAccountWithdraw) => {
        if(history.result_code === '0000' && history.request_type === 6170)
            return 'text-success'
        else if(history.result_code !== '0000')
            return 'text-error'
        else
            return 'text-default'
    }

    const getSuccessResultId = (histories: VirtualAccountWithdraw[]) => {
        const realtime = histories.find(obj => obj.result_code === '0000' && obj.request_type === 6170)
        return realtime ? realtime.id : 0
    }

    const cancelJobs = async (trx_ids: string[]) => {
        if (await alert.value.show('정말 해당건의 출금예약을 취소처리 하시겠습니까?')) {
            const res = await post('/api/v1/manager/virtual-accounts/histories/cancel-job', {
                trx_ids: trx_ids
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

    const settleRetry = async (id: number, user_id:number|null, va_id:number|null, level: number) => {
        if (await alert.value.show('정말 해당건을 재출금시도 하시겠습니까?')) {
            const res = await post('/api/v1/manager/virtual-accounts/histories/retry-settlement', {
                id      : id,
                va_id   : va_id,
                user_id : user_id,
                level   : level,
            }, false)
            snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
        }
        else
            return null
    }

    return {
        isReSettleAble,
        isCollectWithdraw,
        withdrawStatusColors,
        withdrawRetry,
        settleRetry,
        getSuccessResultId,
        cancelJobs,
    }
}

export const useSearchStore = defineStore('WalletHistoryStore', () => {    
    const store = Searcher('virtual-accounts/histories')
    const head  = Header('virtual-accounts/histories', '입출금 내역')
    const getUserHeader = () => {
        return {
            'id': 'NO.',
            'user_name': '상호',
            'account_name': '계좌별칭',
            'account_code': '계좌코드',
        }
    }

    const getTransactionHeader = () => {
        return {
            'trans_type': '거래타입',
            'trans_amount': '거래금액',
            'trx_id': '거래번호',
            'created_at': '생성시간',
        }
    }
    const getDepositHeader = () => {
        return {
            'settle_id': '정산번호',
            'deposit_status': '입금상태',
            'deposit_schedule_time': '입금예정시간',
        }
    }

    const getWithdarwHeader = () => {
        return {
            'withdraw_type'     : '출금타입',
            'withdraw_status'   : '출금상태',
            'withdraw_fee'      : '출금수수료',
            'withdraw_schedule_time': '출금예정시간',
            'withdraw_etc': '더보기',
        }
    }
    const headers: Record<string, string> = {
        ...getUserHeader(),
        ...getTransactionHeader(),
        ...getDepositHeader(),
        ...getWithdarwHeader(),
    }
    const sub_headers: any = []
    head.getSubHeaderCol('소유자 정보', getUserHeader(), sub_headers)
    head.getSubHeaderCol('거래 정보', getTransactionHeader(), sub_headers)
    head.getSubHeaderCol('입금 정보', getDepositHeader(), sub_headers)
    head.getSubHeaderCol('출금 정보', getWithdarwHeader(), sub_headers)

    head.sub_headers.value = sub_headers
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    //입금완료, 입금대기, 출금완료, 출금실패 합계
    const metas = ref([
        {
            icon: 'ph:hand-deposit-bold',
            color: 'warning',
            title: '입금 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'uil:money-withdraw',
            color: 'privacy',
            title: '출금 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'mingcute:transfer-fill',
            color: 'success',
            title: '입출금 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
        {
            icon: 'tabler-currency-won',
            color: 'default',
            title: '출금수수료 합계',
            stats: '0',
            percentage: 0,
            subtitle: '0건',
        },
    ])

    const exporter = async () => {
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        
        for (let i = 0; i < datas.length; i++) {
            if(datas[i]['trans_type']) {
                // 출금
                datas[i]['withdraw_type']   = withdraw_types.find(obj => obj.id === datas[i]['withdraw_type'])?.title
                datas[i]['withdraw_status'] = withdrawStatusNames(datas[i]['withdraw_status']);
                datas[i]['deposit_status']  = '';
            }
            else {
                // 입금
                datas[i]['withdraw_fee']    = '';
                datas[i]['withdraw_type']   = '';
                datas[i]['withdraw_status'] = '';
                datas[i]['deposit_status']  = depositStatusNames(datas[i]['deposit_status'])
            }
            datas[i]['withdraws']       = '';
            datas[i]['trans_type'] = transTypeNames(datas[i]['trans_type'])
        }

        head.exportToExcel(datas)
    }

    const dataToChart = async() => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            const chart = {
                deposit_amount: parseInt(r.data.deposit_amount || 0),
                deposit_count: parseInt(r.data.deposit_count || 0),
                withdraw_amount: parseInt(r.data.withdraw_amount || 0),
                withdraw_count: parseInt(r.data.withdraw_count || 0),
                withdraw_fee_amount: parseInt(r.data.withdraw_fee_amount || 0),
                withdraw_fee_count: parseInt(r.data.withdraw_count || 0),
                total_amount: 0,
                total_count: 0,
            }
            chart.total_amount = chart.deposit_amount + chart.withdraw_amount
            chart.total_count = chart.deposit_count + chart.withdraw_count

            metas.value[0]['stats'] = chart.deposit_amount.toLocaleString() + ' ￦'
            metas.value[0]['percentage'] = chart.deposit_amount ? 100 : 0
            metas.value[0]['subtitle'] = chart.deposit_count.toLocaleString() + '건'
    
            metas.value[1]['stats'] = chart.withdraw_amount.toLocaleString() + ' ￦'
            metas.value[1]['percentage'] = chart.withdraw_amount ? 100 : 0
            metas.value[1]['subtitle'] = chart.withdraw_count.toLocaleString() + '건'

            metas.value[2]['stats'] = chart.total_amount.toLocaleString() + ' ￦'
            metas.value[2]['percentage'] = store.getPercentage(chart.total_amount, chart.deposit_amount)
            metas.value[2]['subtitle'] = chart.total_count.toLocaleString() + '건'

            metas.value[3]['stats'] = chart.withdraw_fee_amount.toLocaleString() + ' ￦'
            metas.value[3]['percentage'] = store.getPercentage(chart.withdraw_fee_amount, chart.withdraw_amount)
            metas.value[3]['subtitle'] = chart.withdraw_fee_count.toLocaleString() + '건'
            console.log(metas.value)
        }
    }
    
    return {
        store,
        head,
        exporter,
        metas,
        dataToChart,
    }
});
