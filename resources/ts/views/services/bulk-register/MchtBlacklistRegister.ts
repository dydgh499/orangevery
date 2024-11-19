import { isEmpty } from '@/@core/utils';
import { lengthValidatorV2 } from '@/@core/utils/validators';
import { Header } from '@/views/headers';
import { MchtBlacklist } from '@/views/types';


export const validateItems = (item: MchtBlacklist, i: number) => {
    if(isEmpty(item.block_reason))
        return [false, (i + 2) + '번째 블랙리스트의 차단사유를 찾을 수 없습니다.']
    else if (typeof lengthValidatorV2(item.resident_num, 14) != 'boolean') 
        return [false, (i + 2) + '번째 가맹점의 주민등록번호 포멧이 정확하지 않습니다.']
    else
        return [true, '']
}

export const useRegisterStore = defineStore('MchtBlacklistRegisterStore', () => {
    const head = Header('mcht-blacklists/bulk-register', '가맹점 블랙리스트 대량등록 포멧')
    const headers = [
        { key: 'mcht_name', title: '가맹점 상호(X)' },
        { key: 'nick_name', title: '대표자명(X)' },
        { key: 'phone_num', title: '전화번호(X)' },
        { key: 'business_num', title: '사업자번호(X)' },
        { key: 'resident_num', title: '주민등록번호(X)' },
        { key: 'addr', title: '주소(X)' },
        { key: 'card_num', title: '카드번호(X)' },
        { key: 'block_reason', title: '차단 사유(O)' },
    ]
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    return {
        head, headers
    }
})
