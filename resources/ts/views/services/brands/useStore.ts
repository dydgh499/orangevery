import { Header } from '@/views/headers'
import { Searcher } from '@/views/searcher'
import type { AuthOption, Brand, FreeOption, PaidOption, ThemeCSS } from '@/views/types'

export const useSearchStore = defineStore('brandSearchStore', () => {
    const store = Searcher('services/brands')
    const head  = Header('services/brands', '서비스 관리')
    const headers: Record<string, string|object> = {
        'id' : 'NO.',
        'dns' : 'DNS',
        'logo_img' : 'LOGO',
        'main_color' : '테마색상',
        'company_nm' : '회사명',
        'ceo_nm' : '대표자명',
        'phone_num' : '연락처',
        'note' : '비고',
        'deposit_day': '입금일',
        'deposit_amount': '입금액',
        'last_dpst_at': '마지막 입금일',
        'created_at' : '생성시간',
        'updated_at' : '업데이트시간',
    }
  
    head.main_headers.value = [
        '서비스 정보',
        '결제 사용여부',
        '입금',
    ];
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.getAllDataFormat())
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
                subsidiary_use_control: false,
            }),
            auth: reactive<AuthOption>({
                levels: {
                    dev_use: false,
                    dev_name: '개발사',
                    sales5_use: true,
                    sales5_name: '지사',
                    sales4_use: false,
                    sales4_name: '하위지사',
                    sales3_use: true,
                    sales3_name: '총판',
                    sales2_use: false,
                    sales2_name: '하위총판',
                    sales1_use: true,
                    sales1_name: '대리점',
                    sales0_use: false,
                    sales0_name: '하위대리점'
                }
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
