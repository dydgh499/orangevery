import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { AuthOption, Brand, FreeOption, Options, PaidOption, ThemeCSS } from '@/views/types';
import { getUserLevel } from '@axios';
import corp from '@corp';

export const isMaster = () => {
    return getUserLevel() >= 50 && corp.id == parseInt(import.meta.env.VITE_MAIN_BRAND_ID as string)
}
export const dev_settle_types = <Options[]>([
    {id:0, title:'적용안함'},
    {id:1, title:'본사이익 대비 방식'},
    {id:2, title:'전체매출 대비 방식'},
    {id:3, title:'수수료 차감 방식'},
])
export const useSearchStore = defineStore('brandSearchStore', () => {
    const store = Searcher('services/brands')
    const head = Header('services/brands', '서비스 관리')
    const headers: Record<string, string | object> = {
        'id': 'NO.',
    }
    if (getUserLevel() >= 50) {
        headers['note'] = '비고'
        headers['last_dpst_at'] = '마지막 입금일'
        headers['deposit_day'] = '입금일'
        headers['deposit_amount'] = '입금액'
        headers['extra_deposit_amount'] = '부가입금액'
        headers['curr_deposit_amount'] = '현재입금액(월)'
    }
    headers['dns'] = 'DNS'
    headers['logo_img'] = 'LOGO'
    headers['main_color'] = '테마색상'
    headers['company_name'] = '회사명'
    headers['ceo_name'] = '대표자명'
    headers['phone_num'] = '연락처'

    if (getUserLevel() >= 50) {
        headers['dev_fee'] = corp.pv_options.auth.levels.dev_name+' 수수료'
        headers['dev_settle_type'] = corp.pv_options.auth.levels.dev_name+' 수수료 정산타입'
        headers['created_at'] = '생성시간'
        headers['updated_at'] = '업데이트시간'
        headers['extra_col'] = '더보기'
    }

    head.sub_headers.value = []
    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.flatten(head.headers.value)

    const metas = ref()

    const boolToText = (col: any) => {
        if(typeof col == 'boolean') {
            return col ? '사용' : '미사용'
        }
        else
            return col
    }
    const exporter = async (type: number) => {
        const keys = Object.keys(head.flat_headers.value)
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
        }
        head.exportToExcel(datas)
    }
    
    if(isMaster()) {
        metas.value = [
            {
                icon: 'ic-outline-payments',
                color: 'primary',
                title: '총 입금액 합계',
                stats: '0',
            },
            {
                icon: 'ic-outline-payments',
                color: 'default',
                title: '입금액 합계',
                stats: '0',
            },
            {
                icon: 'ic-outline-payments',
                color: 'success',
                title: '부가입금액 합계',
                stats: '0',
            },
            {
                icon: 'ic-outline-payments',
                color: 'info',
                title: '현재입금액 합계',
                stats: '0',
            },
        ]
    }
    
    onMounted(async () => {
        if(isMaster()) {
            const r = await store.getChartData()
            if(r.status == 200) {
                metas.value[0]['stats'] = parseInt(r.data.total_deposit_amount).toLocaleString() + ' ₩'
                metas.value[1]['stats'] = parseInt(r.data.deposit_amount).toLocaleString() + '₩'
                metas.value[2]['stats'] = parseInt(r.data.extra_deposit_amount).toLocaleString() + '₩'
                metas.value[3]['stats'] = parseInt(r.data.curr_deposit_amount).toLocaleString() + '₩'
            }
        }
    })

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
        last_dpst_at: null,
        updated_at: null,
        created_at: null,
        deposit_day: 1,
        deposit_amount: 1000000,
        pv_options: {
            free: reactive<FreeOption>({
                use_search_date_detail: true,
                sales_slip: {
                    merchandise: {
                        company_name: '',
                        rep_name: '',
                        phone_num: '',
                        business_num: '',
                        addr: ''
                    }
                },
                bonaeja: {
                    user_id: '',
                    api_key: '',
                    sender_phone: '',
                    receive_phone: '',
                    min_balance_limit: 0
                },
                default: {
                    installment: 0,
                    abnormal_trans_limit: 0,
                    is_show_fee: 0
                },
                use_tid_duplicate: false,
                use_mid_duplicate: false,
                use_fix_table_view: true,
                fix_table_size: 749,
                init_search_filter: false,
                resident_num_masking: false,
                pay_module_detail_view: false,
                secure: {
                    mcht_id_level: 0,
                    mcht_pw_level: 0,
                    account_lock_limit: 0,
                    sales_id_level: 0,
                    sales_pw_level: 0,
                    login_only_operate: 0
                }
            }),
            paid: reactive<PaidOption>({
                use_acct_verification: false,
                use_hand_pay_sms: false,
                use_realtime_deposit: false,
                use_issuer_filter: false,
                use_dup_pay_validation: false,
                use_forb_pay_time: false,
                use_pay_limit: false,
                subsidiary_use_control: false,
                use_online_pay: false,
                use_tid_create: false,
                use_mid_create: false,
                use_pay_verification_mobile: false,
                use_regular_card: false,
                use_collect_withdraw: false,
                use_withdraw_fee: false,
                use_noti: false,
                use_head_office_withdraw: false,
                use_collect_withdraw_scheduler: false,
                use_finance_van_deposit: false,
                use_pmid: false,
                use_before_brand_info: false,
                use_multiple_hand_pay: false,
                use_mcht_blacklist: false,
                use_part_cancel: false,
                use_settle_hold: false,
                use_hide_account: false,
                sales_parent_structure: false,
                use_specified_limit: false,
                use_syslink: false,
                use_product: false
            }),
            auth: reactive<AuthOption>({
                levels: {
                    dev_use: false,
                    dev_name: '개발사',
                    sales5_use: false,
                    sales5_name: '지사',
                    sales4_use: true,
                    sales4_name: '영업점',
                    sales3_use: true,
                    sales3_name: '지사',
                    sales2_use: true,
                    sales2_name: '총판',
                    sales1_use: true,
                    sales1_name: '대리점',
                    sales0_use: true,
                    sales0_name: '하위대리점'
                },
                visibles: {
                    abnormal_trans_sales: true,
                }
            })
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
        is_transfer: 0,
        login_img: null,
        dev_fee: 0,
        dev_settle_type: 0,
        extra_deposit_amount: 0,
        curr_deposit_amount: 0,
        use_different_settlement: 0,
        before_brand_infos: [],
        different_settlement_infos: [],
        operator_ips: []
    })


    return {
        path, item
    }
}
