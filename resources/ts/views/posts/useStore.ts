import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Options, Post } from '@/views/types'
import { getUserLevel } from '@axios'


export const types = <Options[]>([
    { id: 0, title: "공지사항" }, 
    { id: 1, title: "FAQ" },
    { id: 2, title: "1:1 문의" },
])

export const useSearchStore = defineStore('postSearchStore', () => {
    const store = Searcher('posts')
    const head  = Header('posts', '공지사항')
    const headers: Record<string, string> = {
        'id' : 'NO.',
        'type' : '게시글 타입',
        'writer' : '작성자',
        'title' : '제목',
        'created_at' : '생성시간',
    }    
    if(getUserLevel() >= 35)
        headers['extra_col'] = '더보기'
        
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['type'] = types.find(types => types.id === datas[i]['type'])?.title
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
    const path  = 'posts'
    const item  = reactive<Post>({
        id: 0,
        title: '',
        content: '',
        type: 0,
        parent_id: null,
        writer: '',
        is_reply: 0,
        level: 10,
        updated_at: ''
    })
    return {
        path, item
    }
}
