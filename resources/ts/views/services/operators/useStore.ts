import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { Operator, Options } from '@/views/types';

export const operator_levels:Options[] = [
    {id:30, title:'스태프'},
    {id:35, title:'본사'},
    {id:40, title:'협력사'},
    {id:50, title:'개발사'},
]

export const useSearchStore = defineStore('operatorSearchStore', () => {    
    const store = Searcher('services/operators')
    const head  = Header('services/operators', '운영자 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'level' : '등급',
        'user_name' : 'ID',
        'nick_name' : '성명',
        'phone_num' : '연락처',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
    head.main_headers.value = [
        '서비스 정보',
        '결제 사용여부',
        '입금',
    ];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {      
        const r = await store.get(store.getAllDataFormat())
        let convert = r.data.content;
        for (let index = 0; index <convert.length; index++) 
        {
        
        }
        type == 1 ? head.exportToExcel(convert) : head.exportToPdf(convert)        
    }
    return {
        store,
        head,
        exporter,
    }
})

export const useUpdateStore = defineStore('operatorUpdateStore', () => {
    const path  = 'services/operators'
    const item  = reactive<Operator>({
        level: 35,
        id: 0,
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
