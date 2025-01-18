import { isEmpty } from '@/@core/utils';
import { Merchandise, NotiUrl, PayModule } from '@/views/types';

const filterPayModuleNote = (pmod_note: string, mcht_id: number, pay_modules: PayModule[]) => {
    const filter = pay_modules.filter((obj: PayModule) => { return obj.mcht_id === mcht_id })
    return filter.find(obj => obj.note === pmod_note ? pmod_note.trim() : '')?.id
}

export const validateItems = (item: NotiUrl, i: number, mchts: Merchandise[], pay_modules: PayModule[]) => {
    item.mcht_name = item.mcht_name ? item.mcht_name?.trim() : ''
    const mcht = mchts.find(m => m.mcht_name == item.mcht_name)
    if (mcht) {
        item.pmod_id = item.pmod_note == -1 ? -1 : filterPayModuleNote(item.pmod_note, mcht.id, pay_modules) as number
        if (item.pmod_id === null) 
            return [false, (i + 1) + '번째줄의 노티의 결제모듈 별칭이 이상합니다.']
        else if (isEmpty(item.send_url)) 
            return [false, (i + 2) + '번째줄의 노티주소가 비어있습니다.']
        else if((item.noti_status === 0 || item.noti_status === 1) === false)
            return [false, (i + 2) + '번째줄의 노티 사용 유무가 이상합니다.']
        else {
            item.mcht_id = mcht?.id as number
            return [true, '']
        }
    }
    else
        return [false, (i + 2) + '번째줄의 노티의 가맹점 상호가 이상합니다.']
}

export const useRegisterStore = defineStore('NotiUrlRegisterStore', () => {
    const headers = [
        { key: 'mcht_name', title: '가맹점 상호(O)' },
        { key: 'pmod_note', title: '결제모듈 별칭(X)' },
        { key: 'noti_status', title: '노티 사용여부(O)' },
        { key: 'note', title: '별칭(X)' },
        { key: 'send_url', title: '발송 URL(O)' },
    ]
    return {
        headers
    }
})
