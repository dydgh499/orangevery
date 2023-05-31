import { Searcher } from '@/views/searcher';
import type { Operator } from '@/views/types';
import corp from '@corp';

export const operator_levels = [
    {id:35, name:'스태프'},
    {id:35, name:'본사'},
    {id:40, name:'협력사'},
    {id:50, name:'개발사'},
]
export const useSearchStore = defineStore('operatorSearchStore', () => {    
    const store = Searcher<Operator>('services/operators', <Operator>({}))
    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('ID', 'user_name')
        store.setHeader('성명', 'nick_name')
        store.setHeader('휴대폰번호', 'phone_num')
        store.setHeader('등급', 'level')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
        store.sortHeader()
    }
    return {
        store,
        setHeaders,
    }
})

export const useUpdateStore = defineStore('operatorUpdateStore', () => {
    const path  = 'services/operators'
    const item  = reactive<Operator>({
        level: 35,
        id: 0,
        brand_id: corp.brand_id,
        user_name: '',
        user_pw: '',
        nick_name: '',
        phone_num: '',
        created_at: null,
        updated_at: null,
    })
    return {
        path, item
    }
})
