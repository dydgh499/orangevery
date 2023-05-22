
interface ErrorResponse {
    message: string,
}
interface SearchParams {
    page: number,
    page_size: number,
    s_dt: Date,
    e_dt: Date,
    search: string,
}

interface Pagenation {
    total_count: number,
    total_page: number,
}
interface Filter {
    key: string,
    ko: string,
    hidden: boolean,
}

interface Tab {
    icon: string,
    title: string,
}
//----------------- 검색 -------------
export interface UserPropertie {
    id: number
    created_at: datetime
    //    
    brand_id: number
    group_id: number
    user_name: string
    user_pw: string
    nick_name: string
    //
    addr: string
    phone_num: string
    email: string
    resident_num: string
    business_num: string
    sector: string
    passbook_img: File | null
    id_img: File | null
    contract_img: File | null
    bsin_lic_img: File | null
    acct_num: string
    acct_nm: string
    acct_bank_nm: string
    acct_bank_cd: string
}

export interface MerchandisePropertie {
    mcht_name: string
    trx_fee: float
    hold_fee: float
    abnormal_trans_limit: number
    pay_day_limit: number
    pay_year_limit: number
    use_dupe_trx: boolean
    is_show_fee: boolean
}

export interface SalesforcePropertie {
    tax_type: number
    trx_fee: float
}

export interface Merchandise extends MerchandisePropertie, UserPropertie {}

export interface Salesforce extends SalesforcePropertie, UserPropertie {}
// !SECTION

// App: Payment Modules
export interface PayModule {
    id: number
    brand_id: number
    mcht_id: number
    pg_id: number | null
    ps_id: number | null
    withdraw_id: number | null
    terminal_id: number | null
    module_type: number
    api_key: string
    sub_key: string
    mid: string
    tid: string
    serial_num: string
    comm_pr: number
    comm_calc_day: number
    comm_calc_id: number
    under_sales_amt: number
    begin_dt: date
    ship_out_dt: date
    ship_out_stat: boolean
    is_old_auth: boolean
    use_saleslip_prov: boolean
    use_saleslip_sell: boolean
    installment_limit: number
    note: string
}

export interface PayGateway {
    id: number
    brand_id: number
    pg_type: number | null
    pg_nm: string
    rep_nm: string
    company_nm: string
    business_num: string
    phone_num: string
    addr:string
}

export interface PaySection {
    id: number
    brand_id: number
    pg_id: number
    name: string
    trx_fee: float
    is_use: boolean 
}

export interface Classification {
    id: number
    brand_id: number
    name: string
    trx_fee: float
    type: number
}

export interface Brand {
    id: number
    dns: string,
    name:string,
    thme_css: string,
    // 운영 이미지
    logo_img: File | null,
    dark_logo_img: File | null,
    favicon_img: File | null,
    og_img: File | null,
    // 계약 이미지
    passbook_img: File | null,
    id_img: File | null,
    contract_img: File | null,
    bsin_lic_img: File | null,
    // 운영 정보
    og_description: string,
    note: string,
    company_nm: string,
    pvcy_rep_nm: string,
    ceo_nm: string,
    //
    addr: string,
    business_num: string,
    phone_num: string,
    fax_num: string,
    pv_options: string,
    last_dpst_at: datetime,
    updated_at:datetime,
    created_at: datetime
}
