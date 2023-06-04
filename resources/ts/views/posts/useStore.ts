import { Searcher } from '@/views/searcher';
import type { Options, Post } from '@/views/types';

export const types = <Options[]>([
    { id: 0, title: "공지사항" }, 
    { id: 1, title: "FAQ" },
    { id: 2, title: "1:1 문의" },
])

export const useSearchStore = defineStore('postSearchStore', () => {
    const store = Searcher<Post>('posts', <Post>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('타입', 'type')
        store.setHeader('작성자', 'writer')
        store.setHeader('제목', 'title')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
        store.sortHeader()
    }
    setHeaders()
    return {
        store,
    }
})

export const useUpdateStore = defineStore('postUpdateStore', () => {
    const path  = 'posts'
    const item  = reactive<Post>({
        id: 0,
        title: '',
        content: '',
        type: 0,
        parent_id: 0,
        writer: ''
    })
    return {
        path, item
    }
})
