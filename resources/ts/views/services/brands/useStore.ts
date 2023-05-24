import { Searcher } from '@/views/searcher';
import type { Brand } from '@/views/types';

export const useSearchStore = defineStore('brandSearchStore', () => {    
    const store = Searcher<Brand>('services/brands', <Brand>({}))

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('DNS', 'group_name')
        store.setHeader('LOGO', 'user_name')
        store.setHeader('회사명', 'company_nm')
        store.setHeader('대표자명', 'mcht_name')
        store.setHeader('연락처', 'phone_num')
        store.setHeader('옵션', 'pv_options')
        store.setHeader('비고', 'note')
        store.setHeader('마지막 입금일', 'last_dpst_at')
        store.setHeader('생성시간', 'created_at')
        store.setHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
    }
})


export const useUpdateStore = defineStore('brandUpdateStore', () => {
    const path  = 'brands'
    const item  = reactive<Brand>({
        id: 0,
        dns: '',
        name: '',
        thme_css: '',
        logo_img: null,
        dark_logo_img: null,
        favicon_img: null,
        og_img: null,
        passbook_img: null,
        id_img: null,
        contract_img: null,
        bsin_lic_img: null,
        og_description: '',
        note: '',
        company_nm: '',
        pvcy_rep_nm: '',
        ceo_nm: '',
        addr: '',
        business_num: '',
        phone_num: '',
        fax_num: '',
        pv_options: '[]',
        last_dpst_at: null,
        updated_at: null,
        created_at: null,
    })    
    return {
        path, item
    }
})
