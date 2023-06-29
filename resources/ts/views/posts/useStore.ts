import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { Options, Post } from '@/views/types'
import { user_info } from '@axios'


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
        'type' : '타입',
        'writer' : '작성자',
        'title' : '제목',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }    
    if(user_info.value.level >= 35)
        headers['extra_col'] = '더보기'
        
    head.main_headers.value = [];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i]['type'] = types.find(types => types.id === datas[i]['type'])?.title
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

export const useUpdateStore = defineStore('postUpdateStore', () => {
    const path  = 'posts'
    const item  = reactive<Post>({
        id: 0,
        title: '',
        content: '',
        type: 0,
        parent_id: null,
        writer: '',
        is_reply: false
    })
    return {
        path, item
    }
})
