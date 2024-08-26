import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Operator, Options } from '@/views/types';
import { avatars } from '@/views/users/useStore';
import { getUserLevel } from '@axios';

export const operator_levels:Options[] = [
    {id:35, title:'직원'},
    {id:40, title:'본사'},
]

export const useSearchStore = defineStore('operatorSearchStore', () => {    
    const store = Searcher('services/operators')
    const head  = Header('services/operators', '운영자 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'profile_img' : '프로필',
        'level'     : '등급',
        'user_name' : 'ID',
        'nick_name' : '성명',
        'phone_num' : '연락처',
        'is_2fa_use': '2FA 사용',
    }

    if(getUserLevel() >= 35) {
        headers['is_lock'] = '계정잠김여부'
        headers['locked_at'] = '계정잠금시간'
    }
    
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'

    if(getUserLevel() >= 40)
        headers['extra_col'] = '더보기'

    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {

            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})

export const defaultItemInfo = () => {
    const path  = 'services/operators'
    const item  = reactive<Operator>({
        level: 35,
        id: 0,
        user_name: '',
        user_pw: '',
        nick_name: '',
        phone_num: '',
        profile_img: avatars[Math.floor(Math.random() * avatars.length)],
        created_at: null,
        updated_at: null,
    })
    return {
        path, item
    }
}
