

import { useStore } from '@/views/services/options/useStore'
import { isEmpty } from '@core/utils'
import { banks } from '@/views/users/useStore'

export const validateItems = (item: any, i: number) => {    
    const { bill_keys } = useStore()
    if (isEmpty(item.bill_id))
        return [false, (i + 2) + '번째 빌키 카드별칭는 필수로 입력해야합니다.']
    else if (isEmpty(item.acct_bank_code)) 
        return [false, (i + 2) + '번째 입금 은행코드는 필수로 입력해야합니다.']
    else if (isEmpty(item.acct_num)) 
        return [false, (i + 2) + '번째 입금 계좌번호는 필수로 입력해야합니다.']
    else if (isEmpty(item.acct_name)) 
        return [false, (i + 2) + '번째 예금주는 필수로 입력해야합니다.']
    else if (isEmpty(item.amount)) 
        return [false, (i + 2) + '번째 결제금액은 필수로 입력해야합니다.']
    else if (banks.find(bank => bank.title === item.acct_bank_name) == null) 
        return [false, (i + 2) + '번째줄의 은행코드가 이상합니다.']
    else if(Number(item.amount) < 0)
        return [false, (i + 2) + '번째 결제금액이 이상합니다.']
    else {
        let bill_key = bill_keys.find(obj => obj.nick_name === item.bill_id)
        if(bill_key) {
            item.bill_id = bill_key.id
            return [true, '']
        }
        else
            return [false, (i + 2) + '번째 빌키 카드명칭 찾을 수 없습니다.']
    }
}

export const useRegisterStore = defineStore('PaymentRegisterStore', () => {
    const getHeaders = () => {
        return [
            { title: '결제금액(O)', key: 'amount' },
            { title: '빌키 카드별칭(O)', key: 'bill_id' },
            { title: '입금 은행코드(O)', key: 'acct_bank_code'},
            { title: '입금 계좌번호(O)', key: 'acct_num'},
            { title: '예금주(O)', key: 'acct_name'},
        ]
    }
    const headers = getHeaders()

    return {
        headers
    }
})
