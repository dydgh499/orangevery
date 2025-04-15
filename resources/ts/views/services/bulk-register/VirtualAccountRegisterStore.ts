

import { FinanceVan, Merchandise, VirtualAccount } from '@/views/types';
import { fin_trx_delays, withdraw_limit_types, withdraw_types } from '@/views/virtual-accounts/wallets/useStore';

export const validateItems = (item: VirtualAccount, i: number, mchts: Merchandise[], finance_vans:FinanceVan[]) => {
    const mcht_name = item.user_id ? item.user_id.toString()?.trim() : ''
    const fin_trx_delay = fin_trx_delays.find(a => a.id === parseInt(item.fin_trx_delay))
    const withdraw_limit_type = withdraw_limit_types.find(a => a.id === parseInt(item.withdraw_limit_type))
    const withdraw_type = withdraw_types.find(a => a.id === parseInt(item.withdraw_type))
    const finance_van = finance_vans.find(a => a.id === parseInt(item.fin_id))
    const mcht = mchts.find(a => a.mcht_name == mcht_name)

    if (mcht == null)
        return [false, (i + 2) + '번째 가맹점 상호가 이상합니다.(' + mcht_name + ")"]
    else if (finance_van === null || finance_van === undefined) 
        return [false, (i + 2) + '번째 이체모듈이 이상합니다.']
    else if (fin_trx_delay === null || fin_trx_delay === undefined) 
        return [false, (i + 2) + '번째 이체딜레이가 이상합니다.']
    else if (withdraw_limit_type === null || withdraw_limit_type === undefined)
        return [false, (i + 2) + '번째 출금제한타입이 이상합니다.']
    else if (withdraw_type == null || withdraw_type === undefined)
        return [false, (i + 2) + '번째 출금타입이 입력해야합니다.']
    else {
        item.user_id = mcht?.id || null
        return [true, '']
    }
}

export const useRegisterStore = defineStore('virtualAccountRegisterStore', () => {
    const getHeaders = () => {
        return [
            {title: '가맹점 상호(O)', key: 'user_id'},
            {title: '정산지갑 별칭(O)', key: 'account_name'},
            {title: '이체모듈 타입(O)', key: 'fin_id'},
            {title: '이체딜레이(O)', key: 'fin_trx_delay'},
            {title: '출금타입(O)', key: 'withdraw_type'},
            {title: '출금 수수료(O)', key: 'withdraw_fee'},
            {title: '출금제한타입(O)', key: 'withdraw_limit_type'},
            {title: '일 출금한도(영업일)(X)', key: 'withdraw_business_limit'},
            {title: '일 출금한도(휴무일)(X)', key: 'withdraw_holiday_limit'},
        ]
    }
    
    const isPrimaryHeader = (key: string) => {
        const keys = [
            'mcht_name', 'fin_id', 'fin_trx_delay', 'withdraw_type', 
            'withdraw_limit_type',
        ]
        return keys.includes(key)
    }
    const headers = getHeaders()

    return {
        headers, isPrimaryHeader
    }
})
