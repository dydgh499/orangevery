
export type SearchItem = {
    id: number
    url: { name:string, params?: object}
    icon: string
    title: string
    category: string
}
  
export type SearchHeader = {
    header: string
    title:string
}
export interface RegisterResponse {
    accessToken: string
    userData: AuthUserOut
    abilities: UserAbility[]
  }
//------------------------------
export interface ErrorResponse {
    message: string,
}
export interface SearchParams {
    page: number,
    page_size: number,
    s_dt: Date,
    e_dt: Date,
    search: string,
}

export interface Pagenation {
    total_count: number,
    total_page: number,
}
export interface Filter {
    key: string,
    ko: string,
    hidden: boolean,
}

export interface Tab {
    icon: string,
    title: string,
}
//----------------- 검색 -------------
export interface BasePropertie {
    id: number,
    brand_id: number,
    user_name: string,
    user_pw: string,
    nick_name: string,
    phone_num: string,
    created_at: datetime | null,
    updated_at: datetime | null,
}

export interface UserPropertie extends BaseProperties {
    addr: string,
    email: string,
    resident_num: string,
    business_num: string,
    sector: string,
    passbook_img: File | null,
    id_img: File | null,
    contract_img: File | null,
    bsin_lic_img: File | null,
    acct_num: string,
    acct_nm: string,
    acct_bank_nm: string,
    acct_bank_cd: string,
}

export interface MerchandisePropertie {
    group_id: number,
    mcht_name: string,
    trx_fee: float,
    hold_fee: float,
    abnormal_trans_limit: number,
    pay_day_limit: number,
    pay_year_limit: number,
    use_dupe_trx: boolean,
    is_show_fee: boolean,
}

export interface SalesforcePropertie {
    tax_type: number,
    trx_fee: float,
}

export interface Merchandise extends MerchandisePropertie, UserPropertie {}

export interface Salesforce extends SalesforcePropertie, UserPropertie {}

export interface Operator extends BasePropertie{
    level: number
}

export interface PayModule {
    id: number,
    brand_id: number,
    mcht_id: number,
    pg_id: number | null,
    ps_id: number | null,
    withdraw_id: number | null,
    terminal_id: number | null,
    module_type: number,
    api_key: string,
    sub_key: string,
    mid: string,
    tid: string,
    serial_num: string,
    comm_pr: number,
    comm_calc_day: number,
    comm_calc_id: number,
    under_sales_amt: number,
    begin_dt: date,
    ship_out_dt: date,
    ship_out_stat: boolean,
    is_old_auth: boolean,
    use_saleslip_prov: boolean,
    use_saleslip_sell: boolean,
    installment: number,
    note: string,
}

export interface PayGateway {
    id: number,
    brand_id: number,
    pg_type: number | null,
    pg_nm: string,
    rep_nm: string,
    company_nm: string,
    business_num: string,
    phone_num: string,
    addr:string,
}

export interface PaySection {
    id: number,
    brand_id: number,
    pg_id: number,
    name: string,
    trx_fee: float,
    is_use: boolean,
}

export interface Classification {
    id: number,
    brand_id: number,
    name: string,
    trx_fee: float,
    type: number,
}

export interface Brand {
    id: number,
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
    created_at: datetime,
}

export interface Transaction {
    id: number,
    brand_id: number,
    mcht_id: number,
    pg_id: number,
    pg_name: string,
    ps_id: number,
    ps_name: string,
    //
    sf_fee_id: number,
    sf_name: number,
    //
    pmod_id: number,
    custom_filter_id: number,
    withdraw_id: number,
    withdraw_fee: float,
    trx_fee: float,
    hold_fee: float,
    trx_dt: Date,
    trx_tm: Date,
    cxl_dt: Date,
    trx_tm: Date,
    trx_dttm: Date,
    cxl_dttm: Date,
    is_cancel: boolean,
    amount: number,
    trx_type: number,
    ord_num: string,
    trx_id: string,
    cat_id: string,
    //
    ori_trx_id: string,
    //
    card_nm: string,
    card_num: string,
    installment: string,
    //
    issuer: string,
    acquirer: string,
    appr_num: string,
    buyer_nm: string,
    buyer_phone: string,
    item_nm: string,
    danger_type: number,
    danger_check: boolean,
}

export interface Danger {
    id: number,
    mcht_id: number,
    tran_id: number,
    trx_type: number,
    mid: string,
    withdraw_id: number,
    withdraw_fee: float,
    cat_id: string,
    card_nm: string,
    card_num: string,
    installment: string,
    danger_type: number,
    danger_check: boolean,
    ps_id: number,
    ps_name: string,
    ps_id: number,
    ps_name: string,
    trx_dttm: Date,
    cxl_dttm: Date,
    updated_at:datetime,
    created_at: datetime,
}
