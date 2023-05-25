import { Searcher } from '@/views/searcher';
import type { Post } from '@/views/types';

export const useSearchStore = defineStore('postSearchStore', () => {
    const store = Searcher<Post>('posts', <Post>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('작성자', 'use_name')
        store.setHeader('제목', 'title')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
    }
})

export const useUpdateStore = defineStore('postUpdateStore', () => {
    const path  = 'posts'
    const item  = reactive<Post>({
        id: 0,
        brand_id: 0,
        title: '',
        content: '',
        type:0,
        user_id: 0,
        parent_id: null,
        depth: 0
    })
    return {
        path, item
    }
})
