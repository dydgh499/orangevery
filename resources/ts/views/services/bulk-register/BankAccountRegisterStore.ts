

import { VirtualAccount } from '@/views/types';
import { isEmpty } from '@core/utils'
import { banks } from '@/views/users/useStore';

export const validateItems = (item: VirtualAccount, i: number, acct_nums: any) => {
    const acct_bank_name = banks.find(bulk => bulk.title === item.acct_bank_name)
    
    if (isEmpty(item.acct_bank_name)) 
        return [false, (i + 2) + '번째줄의 입금 은행명은 필수로 입력해야합니다.']
    else if (acct_bank_name === null) 
        return [false, (i + 2) + '번째줄의 입금 은행명이 이상합니다.']
    else if (isEmpty(item.acct_num)) 
        return [false, (i + 2) + '번째줄의 입금 계좌번호는 필수로 입력해야합니다.']
    else if (acct_nums.has(item.acct_num))
        return [false, (i + 2) + '번째줄의 입금 계좌번호가 중복됩니다.('+item.acct_num+")"]
    else if (isEmpty(item.acct_name)) 
        return [false, (i + 2) + '번째줄의 예금주는 필수로 입력해야합니다.']
    else {
        item.acct_bank_code = banks.find(bulk => bulk.title === item.acct_bank_name)?.code as string
        console.log('확인', item.acct_bank_name)
        return [true, '']
    }
}

export const useRegisterStore = defineStore('virtualAccountRegisterStore', () => {
    const getHeaders = () => {
        return [
            {title: '입금 은행명(O)', key: 'acct_bank_name'},
            {title: '입금 계좌번호(O)', key: 'acct_num'},
            {title: '예금주(O)', key: 'acct_name'},
        ]
    }
    
    const isPrimaryHeader = (key: string) => {
        const keys = [
            'deposit_acct_num',
        ]
        return keys.includes(key)
    }
    const headers = getHeaders()

    return {
        headers, isPrimaryHeader
    }
})
drwxr-xr-x 
drwxrwxr-x 