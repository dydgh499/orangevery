

import { FinanceVan, Withdraw } from '@/views/types';
import { isEmpty } from '@core/utils'
import { banks } from '@/views/users/useStore';
import corp from '@/plugins/corp';

export const validateItems = (item: Withdraw, i: number, acct_nums: any, finance_vans:FinanceVan[]) => {
    const finance_van = finance_vans.find(a => a.id === parseInt(item.fin_id))
    const acct_bank_name = banks.find(bulk => bulk.title === item.acct_bank_name)
    
    if (finance_van === null || finance_van === undefined) 
        return [false, (i + 2) + '번째 이체모듈 타입이 이상합니다.']
    else if (isEmpty(item.acct_bank_name)) 
        return [false, (i + 2) + '번째줄의 입금 은행명은 필수로 입력해야합니다.']
    else if (acct_bank_name === null) 
        return [false, (i + 2) + '번째줄의 입금 은행명이 이상합니다.']
    else if (isEmpty(item.acct_num)) 
        return [false, (i + 2) + '번째줄의 입금 계좌번호는 필수로 입력해야합니다.']
    else if (corp.pv_options.free.use_account_number_duplicate && acct_nums.has(item.acct_num))
        return [false, (i + 2) + '번째줄의 입금 계좌번호가 중복됩니다.('+item.acct_num+")"]
    else if (isEmpty(item.acct_name)) 
        return [false, (i + 2) + '번째줄의 예금주는 필수로 입력해야합니다.']
    else if (isEmpty(item.withdraw_amount)) 
        return [false, (i + 2) + '번째줄의 출금 금액은 필수로 입력해야합니다.']
    else {
        item.acct_bank_code = banks.find(bulk => bulk.title === item.acct_bank_name)?.code as string
        return [true, '']
    }
}

export const useRegisterStore = defineStore('virtualAccountRegisterStore', () => {
    const getHeaders = () => {
        return [
            {title: '이체모듈 타입(O)', key: 'fin_id'},
            {title: '입금 은행명(O)', key: 'acct_bank_name'},
            {title: '입금 계좌번호(O)', key: 'acct_num'},
            {title: '예금주(O)', key: 'acct_name'},
            {title: '출금 금액(O)', key: 'withdraw_amount'},
            {title: '출금 사유(X)', key: 'note'},
        ]
    }
    
    const isPrimaryHeader = (key: string) => {
        const keys = [
            'acct_num',
        ]
        return keys.includes(key)
    }
    const headers = getHeaders()

    return {
        headers, isPrimaryHeader
    }
})