import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';


export const useSearchStore = defineStore('productSearchStore', () => {    
    const store = Searcher('merchandises/shopping-mall/products')
    const head  = Header('merchandises/shopping-mall/products', '상품 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
        'mcht_name' : '가맹점 상호',
        'pmod_note' : '결제모듈 별칭',
        'pay_window_secure_level' : '결제창 보안등급',
        'is_able_bill_key' : '빌키 가능여부',
        'product_img': '대표 이미지',
        'category_name' : '카테고리명',
        'product_name' : '상품명',
        'product_amount': '상품가격',
        'created_at': '생성시간',
        'updated_at': '업데이트시간',
    }
    
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
