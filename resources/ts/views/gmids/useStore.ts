import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { GMID } from '@/views/types'
import { getUserLevel } from '@axios'

export const useSearchStore = defineStore('GmidSearchStore', () => {
    const store = Searcher('gmids')
    const head  = Header('gmids', 'GMID')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'user_name': 'ID',
        'nick_name' : '대표자명',
        'phone_num' : '연락처',
        'g_mid' : 'GMID',
    };
    if(getUserLevel() >= 35) {
        headers['is_lock'] = '계정잠김여부'
        headers['locked_at'] = '계정잠금시간'
    }
    headers['created_at'] = '생성시간'
    headers['updated_at'] = '업데이트시간'
    if(getUserLevel() >= 35) {
        headers['extra_col'] = '더보기'
    }
    
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async () => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        const datas = r.data.content;
        for (let i = 0; i <datas.length; i++) {
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
    const path = 'gmids'
    const item = reactive<GMID>({
        id: 0,
        user_name: '',
        user_pw: 'abc123!',
        nick_name: '',
        phone_num: '',
        g_mid: '',
        created_at: '',
        updated_at: '',
        auth_infos: []
    })
    return {
        path, item
    }
}
