import { user_info } from '@/plugins/axios';
import { Header } from '@/views/headers';
import { Searcher } from '@/views/searcher';
import type { AuthOption, Brand, FreeOption, Options, PaidOption, ThemeCSS } from '@/views/types';
import corp from '@corp';

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
        'dns': 'DNS',
        'logo_img': 'LOGO',
        'main_color': '테마색상',
        'company_name': '회사명',
        'ceo_name': '대표자명',
        'phone_num': '연락처',
        'free': {
            'use_hand_pay': '수기결제',
            'use_auth_pay': '인증결제',
            'use_simple_pay': '간편결제',
        },
    }
    head.main_headers.value = [
        '서비스 정보',
        '무료옵션',
    ]
    if (user_info.value.level == 50) {
        headers['paid'] = {
            'subsidiary_use_control': '가맹점 전산 ON/OFF',
            'use_acct_verification': '예금주 검증',
            'use_dup_pay_validation': '중복결제',
            'use_forb_pay_time': '결제금지시간',
            'use_pay_limit': '결제한도',
            'use_hand_pay_drct': '수기결제 직접입력',
            'use_hand_pay_sms': '수기결제 SMS',
            'use_issuer_filter': '카드사 필터링',
            'use_realtime_deposit': '실시간 결제모듈',
            'use_online_pay': '온라인 결제',
        }
        headers['deposit_day'] = '입금일'
        headers['deposit_amount'] = '입금액'
        headers['extra_deposit_amount'] = '부가입금액'
        headers['curr_deposit_amount'] = '현재입금액(월)'
        headers['dev_fee'] = corp.pv_options.auth.levels.dev_name+' 수수료'
        headers['dev_settle_type'] = corp.pv_options.auth.levels.dev_name+' 수수료 정산타입'
        headers['last_dpst_at'] = '마지막 입금일'
        headers['note'] = '비고'
        headers['created_at'] = '생성시간'
        headers['updated_at'] = '업데이트시간'
        headers['extra_col'] = '더보기'
        head.main_headers.value.push('유료옵션')
    }

    head.headers.value = head.initHeader(headers, {})
    head.flat_headers.value = head.setFlattenHeaders()

    const boolToText = (col: any) => {
        if(typeof col == 'boolean') {
            return col ? '사용' : '미사용'
        }
        else
            return col
    }
    const exporter = async (type: number) => {
        const keys = Object.keys(headers);
        const r = await store.get(store.base_url, { params:store.getAllDataFormat()})
        let datas = r.data.content;
        for (let i = 0; i < datas.length; i++) {
            datas[i] = head.sortAndFilterByHeader(datas[i], keys)
            if (user_info.value.level == 50)
            {
                datas[i].free.use_hand_pay = boolToText(datas[i].free.use_hand_pay)
                datas[i].free.use_auth_pay = boolToText(datas[i].free.use_auth_pay)
                datas[i].free.use_simple_pay = boolToText(datas[i].free.use_simple_pay)

                datas[i].paid.subsidiary_use_control = boolToText(datas[i].paid.subsidiary_use_control)
                datas[i].paid.use_acct_verification = boolToText(datas[i].paid.use_acct_verification)
                datas[i].paid.use_dup_pay_validation = boolToText(datas[i].paid.use_dup_pay_validation)

                datas[i].paid.use_forb_pay_time = boolToText(datas[i].paid.use_forb_pay_time)
                datas[i].paid.use_pay_limit = boolToText(datas[i].paid.use_pay_limit)
                datas[i].paid.use_hand_pay_drct = boolToText(datas[i].paid.use_hand_pay_drct)

                datas[i].paid.use_hand_pay_sms = boolToText(datas[i].paid.use_hand_pay_sms)
                datas[i].paid.use_issuer_filter = boolToText(datas[i].paid.use_issuer_filter)
                datas[i].paid.use_realtime_deposit = boolToText(datas[i].paid.use_realtime_deposit)
            }
        }
        type == 1 ? head.exportToExcel(datas) : head.exportToPdf(datas)
    }
    return {
        store,
        head,
        exporter,
        boolToText,
    }
})


export const defaultItemInfo = () => {
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
        company_name: '',
        pvcy_rep_name: '',
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
                use_hand_pay: false,
                use_auth_pay: false,
                use_simple_pay: false,
                use_search_date_detail: true,
                sales_slip: {
                    merchandise: {
                        comepany_name: '',
                        rep_name: '',
                        phone_num: '',
                        business_num: '',
                        addr: ''
                    }
                },
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
                use_online_pay: false,
                use_tid_create: false,
                use_mid_batch: false,
                use_tid_batch: false,
                use_api_key_batch: false,
                use_sub_key_batch: false,
                use_pay_verification_mobile: false
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
                    sales0_use: false,
                    sales0_name: '하위대리점'
                },
                bonaeja: {
                    user_id: '',
                    api_key: '',
                    sender_phone: ''
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
        rep_mcht_id: '',
        is_use_different_settlement: false,
        above_pg_type: 0
    })


    return {
        path, item
    }
}
