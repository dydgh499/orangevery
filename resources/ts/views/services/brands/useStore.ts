import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { AuthOption, Brand, FreeOption, PaidOption, ThemeCSS } from '@/views/types';
import { getUserLevel } from '@axios';

export const useSearchStore = defineStore('brandSearchStore', () => {
    const store = Searcher('services/brands')
    const head = Header('services/brands', '서비스 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
    }
    if (getUserLevel() >= 50) {
        headers['note'] = '비고'
    }
    headers['dns'] = 'DNS'
    headers['logo_img'] = 'LOGO'
    headers['main_color'] = '테마색상'
    headers['company_name'] = '회사명'
    headers['ceo_name'] = '대표자명'
    headers['phone_num'] = '연락처'

    if (getUserLevel() >= 50) {
        headers['created_at'] = '생성시간'
        headers['updated_at'] = '업데이트시간'
        headers['extra_col'] = '더보기'
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref([])

    const boolToText = (col: any) => {
        if(typeof col == 'boolean') {
            return col ? '사용' : '미사용'
        }
        else
            return col
    }
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
        boolToText,
        metas,
    }
})


export const defaultItemInfo = () => {
    const path = 'services/brands'

    const freeOption = () => {
        return <FreeOption>({
            use_account_number_duplicate: 0,
            bonaeja: {
                user_id: '',
                api_key: '',
                sender_phone: '',
                receive_phone: '',
                min_balance_limit: 0
            },
        })
    }

    const paidOption = () => {
        return <PaidOption>({})
    }

    const authOption = () => {
        return <AuthOption>({})
    }

    const item = reactive<Brand>({
        id: 0,
        dns: '',
        name: '',
        logo_img: '/utils/icons/img-preview.svg',
        favicon_img: '/utils/icons/img-preview.svg',
        og_img: '/utils/icons/img-preview.svg',
        passbook_img: '/utils/icons/img-preview.svg',
        id_img: '/utils/icons/img-preview.svg',
        contract_img: '/utils/icons/img-preview.svg',
        bsin_lic_img: '/utils/icons/img-preview.svg',
        og_description: '',
        note: '',
        company_name: '',
        ceo_name: '',
        addr: '',
        business_num: '',
        phone_num: '',
        fax_num: '',
        updated_at: null,
        created_at: null,
        ov_options: {
            free: reactive<FreeOption>(freeOption()),
            paid: reactive<PaidOption>(paidOption()),
            auth: reactive<AuthOption>(authOption()),
        },
        theme_css: reactive<ThemeCSS>({
            main_color: '#5E35B1',
        }),
        logo_file: undefined,
        favicon_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined,
        og_file: undefined,
        id_file: undefined,
        login_file: undefined,
        login_img: '',
    })

    return {
        path, item
    }
}
