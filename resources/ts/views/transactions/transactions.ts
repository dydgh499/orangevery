import { useRequestStore } from '@/views/request';
import type { NotiSendHistory, RealtimeHistory, Transaction } from '@/views/types';
import { getUserLevel } from '@axios';
import { StatusColors } from '@core/enums';
import corp from '@corp';

export const notiSendHistoryInterface = () => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const { remove, post } = useRequestStore()

    const notiSendDetailClass = (history: NotiSendHistory) => {
        if(history.http_code === 200 || history.http_code === 201)
            return ''
        else 
            return 'text-error'
    }
        
    const notiSendResult = (item: Transaction) => {
        if(item.noti_send_histories?.length === 0) {
            const trans_at = (new Date(item.created_at as string)).getTime() + 30000
            const offset_at = new Date(trans_at) - new Date() 
            if(offset_at < 0)
                return StatusColors.Default
            else
                return StatusColors.Processing
        }
        else {
            const is_success = item.noti_send_histories?.find(obj => obj.http_code === 200 || obj.http_code === 201)
            if(is_success)  //성공
                return StatusColors.Success
            else
                return StatusColors.Error
        }
    }

    const notiSendMessage = (item: Transaction):string => {
        const code = notiSendResult(item)
        if(code === StatusColors.Default)
            return 'N/A'
        else if(code === StatusColors.Processing)
            return '처리중'
        else if(code === StatusColors.Success)
            return '성공'
        else if(code === StatusColors.Error)
            return '실패'
        else
            return '알수없는 상태'
    }

    const notiSelfSend = async (select_idxs: number[]) => {
        if(select_idxs.length) {
            if (await alert.value.show('정말 일괄 재발송하시겠습니까?')) {
                const res = await post(`/api/v1/manager/merchandises/noti-send-histories/self-retry`, {
                    trx_ids: select_idxs 
                })
                snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
            }    
        }
        else
            snackbar.value.show('매출을 1개이상 선택해주세요.', 'error')
    }

    const notiSend = async (trx_ids: number[]) => {
        if(trx_ids.length) {
            if (await alert.value.show('정말 재발송을 하시겠습니까?')) {
                const res = await post(`/api/v1/manager/merchandises/noti-send-histories/retry`, {
                    trx_ids: trx_ids 
                })
                snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
            }
        }
        else
            snackbar.value.show('노티이력을 1개이상 선택해주세요.', 'error')
    }

    const notiRemove = async (item: NotiSendHistory) => {
        remove('/merchandises/noti-send-histories', item, false)
    }

    return {
        notiSendResult,
        notiSendMessage,
        notiSendDetailClass,
        notiSelfSend,
        notiSend,
        notiRemove,
    }
}

export const realtimeHistoryInterface = (formatTime: any) => {
    const alert = <any>(inject('alert'))
    const snackbar = <any>(inject('snackbar'))
    const errorHandler = <any>(inject('$errorHandler'))
    const { post } = useRequestStore()

    const realtimeDetailClass = (history: RealtimeHistory) => {
        if(history.result_code === '0000' && history.request_type === 6170)
            return 'text-success'
        else if(history.result_code !== '0000')
            return 'text-error'
        else
            return 'text-default'
    }
    
    const realtimeResult = (item: Transaction) => {
        if(item.is_cancel)
            return StatusColors.Default
        //실시간 수수료 존재시(실시간 사용)    
        const is_success = item.realtimes?.find(obj => obj.result_code === '0000' && obj.request_type === 6170)
        const is_sending = item.realtimes?.find(obj => obj.result_code === '0050' && obj.request_type === 6170)
        const is_cancel = item.realtimes?.find(obj => obj.result_code === 'PV484')
        const is_error  = item.realtimes?.find(obj => obj.result_code !== '0000' && obj.result_code !== '0050')
        const is_deposit_cancel_job  = item.realtimes?.find(obj => obj.result_code === '-5')
    
        if(is_success)  //성공
            return StatusColors.Success
        if(is_sending)  // 처리중
            return StatusColors.Processing
        if(is_cancel)   // 취소
            return StatusColors.Cancel
        if(is_deposit_cancel_job)
            return StatusColors.DepositCancelJob
        if(item.use_realtime_deposit == 0) // 사용안함
            return StatusColors.Default
        if(is_error)    // 에러
            return StatusColors.Error
    
        if(item.fin_trx_delay as number < 0 && item.realtimes?.length === 0)    // 모아서 출금
            return StatusColors.Info
        if(item.realtimes?.length === 0) //요청 대기
        {
            const retry_able_at = (new Date(item.trx_dttm as string)).getTime() + (item.fin_trx_delay as number * 60000)
            const offset_at = new Date(retry_able_at) - new Date() 
    
            if(offset_at > 0) //요청 대기
                return StatusColors.Primary
            else //대기시간 초과
                return StatusColors.Timeout
        }
    }
    const realtimeMessage = (item: Transaction):string => {
        const code = realtimeResult(item)
        if(code === StatusColors.Default)
            return 'N/A'
        else if(code === StatusColors.Primary) {
            const retry_able_time = (new Date(item.trx_dttm as string)).getTime() + (item.fin_trx_delay as number * 60000)
            return formatTime(new Date(retry_able_time))+'초 이체예정'
        }
        else if(code === StatusColors.Success)
            return '성공'
        else if(code === StatusColors.Processing)
            return '결과 처리중'
        else if(code === StatusColors.Info)
            return '모아서 출금예정'
        else if(code === StatusColors.DepositCancelJob)
            return '이체예약취소'
        else if(code === StatusColors.Error)
            return '실패'
        else if(code === StatusColors.Cancel)
            return '취소'
        else if(code === StatusColors.Timeout)
            return '이체예정시간 초과'
        else
            return '알수없는 상태'
    }
    
    const realtimeRetryAble = (item: Transaction) => {
        const result = realtimeResult(item)
        if(result == StatusColors.Error || result == StatusColors.Timeout ||  result == StatusColors.DepositCancelJob)
            return true
        else
            return false
    }
    
    const isRealtimeTransaction = () => {
        return getUserLevel() >= 35 && corp.pv_options.paid.use_realtime_deposit
    }

    const singleDepositCancelJobReservation = async (trx_ids: number[]) => {
        if (await alert.value.show('정말 해당 거래건을 이체예약취소처리 하시겠습니까?')) {
            const res = await post('/api/v1/manager/transactions/batch-updaters/single-deposit-cancel-job-reservation', {
                selected_idxs: trx_ids
            }, true)
            snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
        }
    }

    const realtimeRetry = async (item: Transaction) => {
        if (await alert.value.show('정말 해당 거래건을 재이체(정산) 하시겠습니까?')) {
            const params = {
                'trx_id': item.id,
                'pmod_id': item.pmod_id,
            }
            const res = await post('/api/v1/manager/transactions/settle-histories/merchandises/single-deposit', params, false)
            snackbar.value.show(res.data.message, res.status === 201 ? 'success' : 'error')
            
        }
        else
            return null
    }

    return {
        realtimeResult,
        realtimeMessage,
        realtimeDetailClass,
        realtimeRetryAble,
        realtimeRetry,
        isRealtimeTransaction,
        singleDepositCancelJobReservation,
    }
}


