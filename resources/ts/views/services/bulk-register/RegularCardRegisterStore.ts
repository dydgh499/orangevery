import { isEmpty } from '@/@core/utils';
import { Merchandise, RegularCreditCard } from '@/views/types';

export const validateItems = (item: RegularCreditCard, i: number, mchts:Merchandise[]) => {
    item.mcht_name = item.mcht_name ? item.mcht_name?.trim() : ''
    const mcht = mchts.find(item => item.mcht_name == item.mcht_name)
    if (mcht == null) 
        return [false, (i + 2) + '번째줄의 카드정보의 가맹점 상호가 이상합니다.']
    else if(isEmpty(item.card_num)) 
        return [false, (i + 2) + '번째줄의 카드정보의 카드번호를 찾을 수 없습니다.']
    else {
        item.mcht_id = mcht?.id as number
        return [true, '']
    }
}

export const useRegisterStore = defineStore('regularCardRegisterStore', () => {
    const headers = [
        { key: 'mcht_name', title : '가맹점 상호(O)'},
        { key: 'note', title: '별칭(O)'},
        { key: 'card_num', title: '카드번호(O)'},
    ]

    return {
        headers
    }
})
