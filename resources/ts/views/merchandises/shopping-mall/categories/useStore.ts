import { axios, getUserLevel } from '@/plugins/axios';
import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import { Category } from '@/views/types';

export const useSearchStore = defineStore('categorySearchStore', () => {    
    const store = Searcher('merchandises/shopping-mall/categories')
    const head  = Header('merchandises/shopping-mall/categories', '카테고리 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'mcht_name' : '가맹점 상호',
        'category_name' : '카테고리명',
        'product_count' : '보유 상품 개수',
        'created_at': '생성시간',
        'updated_at': '업데이트시간',
    }
    if(getUserLevel() >= 35)
        headers['preview'] = '미리보기'
    
    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)
    const metas = ref([])

    const exporter = async () => {
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
        metas,
    }
});

export const useCategoryStore = defineStore('useCategoryStore', () => {
    const categories = ref(<Category[]>([]))
    const errorHandler = <any>(inject('$errorHandler'))
    const snackbar = <any>(inject('snackbar'))
    
    onMounted(async () => {
        await getAllCategories()
    })

    const getAllCategories = async() => {
        try {
            const r = await axios.get('/api/v1/manager/merchandises/shopping-mall/categories', {
                params: {
                    page: 1,
                    page_size: 99999,
                }
            })
            Object.assign(categories.value, r.data.content.sort((a:Category, b:Category) => a.category_name.localeCompare(b.category_name)))    
            return true
        }
        catch (error: any) {
            await nextTick()
            snackbar.value?.show(error.response?.data?.message || 'Error occurred', 'error')
            errorHandler?.(error)
            return false
        }    
    }

    return {
        categories
    }
})
