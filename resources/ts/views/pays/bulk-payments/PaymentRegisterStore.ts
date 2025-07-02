

import { BulkPayment } from '@/views/types';
import { isEmpty } from '@core/utils'

export const validateItems = (item: BulkPayment, i: number) => {
    
    if (isEmpty(item.mid)) 
        return [false, (i + 2) + '번째줄의 mid는 필수로 입력해야합니다.']
    else if (isEmpty(item.buyer_name)) 
        return [false, (i + 2) + '번째줄의 구매자명은 필수로 입력해야합니다.']
    else if (isEmpty(item.buyer_phone)) 
        return [false, (i + 2) + '번째줄의 구매자 전화번호는 필수로 입력해야합니다.']
    else if (isEmpty(item.item_name)) 
        return [false, (i + 2) + '번째줄의 상품명은 필수로 입력해야합니다.']
    else if (isEmpty(item.bill_key)) 
        return [false, (i + 2) + '번째줄의 빌키는 필수로 입력해야합니다.']
    else if (isEmpty(item.amount)) 
        return [false, (i + 2) + '번째줄의 결제금액은 필수로 입력해야합니다.']
    else {
        return [true, '']
    }
}

export const useRegisterStore = defineStore('virtualAccountRegisterStore', () => {
    const getHeaders = () => {
        return [
            {title: 'MID(O)', key: 'mid'},
            {title: 'TID(X)', key: 'tid'},
            {title: '구매자명(O)', key: 'buyer_name'},
            {title: '구매자 전화번호(O)', key: 'buyer_phone'},
            {title: '상품명(O)', key: 'item_name'},
            {title: '빌키(O)', key: 'bill_key'},
            {title: '결제금액(O)', key: 'amount'},
        ]
    }
    
    const isPrimaryHeader = (key: string) => {
        const keys = [
            'mid', 'buyer_name', 'bill_key', 'amount'
        ]
        return keys.includes(key)
    }
    const headers = getHeaders()

    return {
        headers, isPrimaryHeader
    }
})