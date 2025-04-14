import { useRequestStore } from '@/views/request';
import type { NotiSendHistory, Transaction } from '@/views/types';
import { StatusColors } from '@core/enums';

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
        if(item.use_noti) {
            if(item.noti_send_histories?.length === 0) {
                const trans_at = (new Date(item.created_at as string)).getTime() + 30000
                const offset_at = new Date(trans_at) - new Date() 
                if(offset_at < 0)
                {
                    if(item?.noti_status === -1)
                        return StatusColors.Default
                    else
                        return StatusColors.Timeout
                }
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
        else
            return StatusColors.Default
    }

    const notiSendMessage = (item: Transaction):string => {
        const code = notiSendResult(item)
        if(code === StatusColors.Default)
            return 'N/A'
        else if(code === StatusColors.Processing)
            return '처리중'
        else if(code === StatusColors.Timeout)
            return '노티예정시간 초과'
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
