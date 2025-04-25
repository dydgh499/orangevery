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

export const depositTypeNames = (type: number) => {
    if(type === 0)
        return '승인'
    else if(type === 1)
        return '취소'
    else if(type === 2)
        return '통신비'
    else if(type === 3)
        return '취소입금'
    else
        return '알수없음'
}
export const depositTypeColors = (type: number) => {
    if(type === 0)
        return 'primary'
    else if(type === 1)
        return 'error'
    else if(type === 2)
        return 'warning'
    else
        return 'info'
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

    const existWithdraw = (transaction: Transaction) => {
        if(transaction.withdraw_histories?.length) 
            return transaction.withdraw_histories?.find(obj => obj.withdraw_schedule_time !== null) ? true : false
        else
            return false
    }

    const existDeposit = (transaction: Transaction) => {
        if(transaction.withdraw_histories?.length) 
            return transaction.withdraw_histories?.find(obj => obj.deposit_schedule_time !== null) ? true : false
        else
            return false
    }


    const getHistory = (transaction: Transaction, type: number) => {
        if(type === 0)
            return transaction.withdraw_histories?.find(obj => obj.withdraw_schedule_time !== null)
        else
            return transaction.withdraw_histories?.find(obj => obj.deposit_schedule_time !== null)
    }

    const isReSettleAble = (transaction: Transaction) => {
        if(transaction.withdraw_histories?.length) 
            return false
        else {
            if(transaction.use_realtime_deposit && transaction.va_id) {
                if(transaction.mcht_settle_id === null)
                    return true
            }    
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
        getHistory,
        existWithdraw,
        existDeposit,
        isReSettleAble,
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
            'updated_at': '업데이트시간',
        }
    }
    const getDepositHeader = () => {
        return {
            'deposit_type': '입금타입',
            'deposit_status': '입금상태',
            'settle_id': '정산번호',
            'deposit_schedule_time': '입금예정시간',
        }
    }

    const getWithdarwHeader = () => {
        return {
            'withdraw_type'     : '출금타입',
            'withdraw_status'   : '출금상태',
            'withdraw_amount'   : '출금금액',
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


    const exporter = async () => {
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        
        for (let i = 0; i < datas.length; i++) {
            if(datas[i]['trans_type']) {
                // 출금
                datas[i]['withdraw_type']   = withdraw_types.find(obj => obj.id === datas[i]['withdraw_type'])?.title
                datas[i]['withdraw_status'] = withdrawStatusNames(datas[i]['withdraw_status']);
                datas[i]['deposit_status']  = '';
                datas[i]['withdraw_amount'] = datas[i]['trans_amount'] - datas[i]['withdraw_fee'];
            }
            else {
                // 입금
                datas[i]['deposit_type']  = depositTypeNames(datas[i]['deposit_type']);
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
    
    return {
        store,
        head,
        exporter,
    }
});
