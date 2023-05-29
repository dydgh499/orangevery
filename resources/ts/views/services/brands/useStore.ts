import { Searcher } from '@/views/searcher';
import type { Brand, FreeOption, PaidOption, ThemeCSS } from '@/views/types';

export const useSearchStore = defineStore('brandSearchStore', () => {
    const store = Searcher<Brand>('services/brands', <Brand>({}))
    setHeaders()
    setExtraHeaders()

    function setHeaders() {
        store.setHeader('NO.', 'id')
        store.setHeader('DNS', 'dns')
        store.setHeader('LOGO', 'logo_img')
        store.setHeader('테마색상', 'main_color')
        store.setHeader('회사명', 'company_nm')
        store.setHeader('대표자명', 'mcht_name')
        store.setHeader('연락처', 'phone_num')
        store.setHeader('비고', 'note')
        store.setHeader('마지막 입금일', 'last_dpst_at')
    }
    function setExtraHeaders() {
        store.setExtraHeader('개발사 사용여부', 'pv_options.free.use_devloper')
        store.setExtraHeader('수기 사용여부', 'pv_options.free.use_hand_pay')
        store.setExtraHeader('인증 사용여부', 'pv_options.free.use_auth_pay')
        store.setExtraHeader('간편 사용여부', 'pv_options.free.use_simple_pay')
        store.setExtraHeader('입금일', 'deposit_day')
        store.setExtraHeader('입금액', 'deposit_amount')
        store.setExtraHeader('마지막 입금일', 'last_dpst_at')
        store.setExtraHeader('생성시간', 'created_at')
        store.setExtraHeader('업데이트시간', 'updated_at')
    }
    return {
        store,
        setHeaders,
        setExtraHeaders,
    }
})


export const useUpdateStore = defineStore('brandUpdateStore', () => {
    const path = 'services/brands'
    const item = reactive<Brand>({
        id: 0,
        dns: '',
        name: '',
        logo_img: '/icons/img-preview.svg',
        favicon_img: '/icons/img-preview.svg',
        og_img: '/icons/img-preview.svg',
        passbook_img: '/icons/img-preview.svg',
        id_img: '/icons/img-preview.svg',
        contract_img: '/icons/img-preview.svg',
        bsin_lic_img: '/icons/img-preview.svg',
        og_description: '',
        note: '',
        company_nm: '',
        pvcy_rep_nm: '',
        ceo_nm: '',
        addr: '',
        business_num: '',
        phone_num: '',
        fax_num: '',
        last_dpst_at: null,
        updated_at: null,
        created_at: null,
        deposit_day: 1,
        deposit_amount: 1000000,
        pv_options: {
            free: reactive<FreeOption>({
                use_devloper: false,
                use_hand_pay: false,
                use_auth_pay: false,
                use_simple_pay: false,
                sales_slip: {
                    merchandise: {
                        rep_nm: '',
                        phone_num: '',
                        resident_num: '',
                        business_num: '',
                        addr: ''
                    }
                }
            }),
            paid: reactive<PaidOption>({
                use_acct_verification: false,
                use_hand_pay_drct: false,
                use_hand_pay_sms: false,
                use_realtime_deposit: false,
                use_issuer_filter: false,
                use_dup_pay_validation: false,
                use_forb_pay_time: false,
                use_pay_limit: false,
                subsidiary_use_control: false
            })
        },
        theme_css: reactive<ThemeCSS>({
            main_color: '#5E35B1FF',
        }),
        logo_file: undefined,
        favicon_file: undefined,
        og_file: undefined,
        id_file: undefined,
        passbook_file: undefined,
        contract_file: undefined,
        bsin_lic_file: undefined
    })


    return {
        path, item
    }
})
