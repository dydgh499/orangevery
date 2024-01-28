import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Popup } from '@/views/types'

export const useSearchStore = defineStore('popupSearchStore', () => {
    const store = Searcher('popups')
    const head  = Header('popups', '공지사항')
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'type' : '타입',
        'writer' : '작성자',
        'profile_img' : '프로필',
        'popup_title' : '제목',
        'open_range' : '팝업 오픈 기간',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
        
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
    }
})

export const defaultItemInfo = () => {
    const path  = 'popups'
    const item  = reactive<Popup>({
        id: 0,
        user_name: '',
        profile_img: '',
        popup_title: '',
        popup_content: '',
        open_s_dt: '',
        open_e_dt: ''
    })
    return {
        path, item
    }
}
