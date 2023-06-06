
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
//------------------------------
export interface Options {
    id: number,
    title: string,
}

export interface ErrorResponse {
    message: string,
}

export interface CustomFilter {
    pg_id: number | null,
    ps_id: number | null,
    pay_cond_id: number | null,
    terminal_id: number | null,
    custom_id: number | null,
}

export interface SalesForceFilter {
    sales5_id: number | null,
    sales4_id: number | null,
    sales3_id: number | null,
    sales2_id: number | null,
    sales1_id: number | null,
    sales0_id: number | null,
}

export interface SearchParams {
    page: number,
    page_size: number,
    s_dt: Date,
    e_dt: Date,
    custom: CustomFilter,
    sales: SalesForceFilter,
}

export interface Pagenation {
    total_count: number,
    total_page: number,
}
export interface Filter {
    [key: string ]: {
        ko: string,
        hidden: boolean,  
        idx: number,  
    };
}

export interface Tab {
    icon: string,
    title: string,
}
//----------------- 검색 -------------
export interface Bank {
    acct_bank_nm: string,
    acct_bank_cd: string,
    acct_num: string,
    acct_nm: string,
}

export interface Contract {
    id_img: string | null,
    passbook_img: string | null,
    contract_img: string | null,
    bsin_lic_img: string | null,
    id_file: File | undefined,
    passbook_file: File | undefined,
    contract_file: File | undefined,
    bsin_lic_file: File | undefined,
}

export interface BasePropertie {
    id: number,
    user_name: string,
    user_pw: string,
    nick_name: string,
    phone_num: string,
    created_at: datetime | null,
    updated_at: datetime | null,
}
export interface UserPropertie extends BasePropertie, Bank, Contract {
    addr: string,
    resident_num: string,
    business_num: string,
    sector: string,
}

export interface MchtOption {
    abnormal_trans_limit: number,
    pay_day_limit: number,
    pay_month_limit: number,
    pay_year_limit: number,
    pay_dupe_limit: number,
    is_show_fee: boolean,
}
export interface MerchandisePropertie {
    sales5_id: number | null,
    sales5_fee: float,
    sales4_id: number | null,
    sales4_fee: float,
    sales3_id: number | null,
    sales3_fee: float,
    sales2_id: number | null,
    sales2_fee: float,
    sales1_id: number | null,
    sales1_fee: float,
    sales0_id: number | null,
    sales0_fee: float,
    mcht_name: string,
    sector: string,
    trx_fee: float,
    hold_fee: float,
    enabled: boolean,
    custom_id: number | null,
    pv_options: MchtOption,
}

export interface SalesforcePropertie {
    sector: string,
    tax_type: number,
    level: number,
}

export interface Merchandise extends MerchandisePropertie, UserPropertie {}

export interface Salesforce extends SalesforcePropertie, UserPropertie {}

export interface Operator extends BasePropertie{
    level: number
}

export interface PayModule {
    id: number,
    mcht_id: number,
    pg_id: number | null,
    ps_id: number | null,
    pay_cond_id: number | null,
    terminal_id: number | null,
    module_type: number,
    api_key: string,
    sub_key: string,
    mid: string,
    tid: string,
    serial_num: string,
    comm_pr: number,
    comm_calc_day: number,
    comm_calc_level: number,
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
    id: number | null,
    pg_type: number | null,
    pg_nm: string | null,
    rep_nm: string,
    company_nm: string,
    business_num: string,
    phone_num: string,
    addr:string,
}

export interface PaySection {
    id: number,
    pg_id: number,
    name: string,
    trx_fee: float,
    is_use: boolean,
}

export interface Classification {
    id: number,
    name: string,
    trx_fee: float,
    type: number,
}

interface FreeOption {
    use_devloper: boolean,
    use_hand_pay: boolean,
    use_auth_pay: boolean,
    use_simple_pay: boolean,
    sales_slip: {
        merchandise: {
            rep_nm: string,
            phone_num: string,
            resident_num: string,
            business_num: string,
            addr: string,
        }
    }
}
interface PaidOption {
    use_acct_verification: boolean, // 예금주 검증
    use_hand_pay_drct: boolean, // 수기결제 직접입력(가맹점)
    use_hand_pay_sms: boolean, // 수기결제 SMS
    use_realtime_deposit: boolean,  // 실시간 결제모듈
    use_issuer_filter: boolean, // 카드사 필터링
    use_dup_pay_validation: boolean,    // 중복결제 검증 사용 여부
    use_forb_pay_time: boolean,   // 결제금지시간 지정 사용 여부
    use_pay_limit: boolean,   // 결제한도 지정 사용 여부
    subsidiary_use_control: boolean, // 하위 영업점 전산 사용 ON/OFF
}
interface ThemeCSS {
    main_color: string,
}


export interface Brand extends Contract {
    id: number,
    dns: string,
    name:string,
    theme_css: ThemeCSS,
    // 운영 이미지
    logo_img: string | null,
    favicon_img: string | null,
    og_img:string | null,
    logo_file: File | undefined,
    favicon_file: File | undefined,
    og_file: File | undefined,
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
    pv_options: {
        free: FreeOption,
        paid: PaidOption,
    },
    deposit_day: number,
    deposit_amount: number,    
    last_dpst_at: datetime,
    updated_at: datetime,
    created_at: datetime,
}

export interface Transaction {
    id: number,
    brand_id: number,
    mcht_id: number,

    sales5_id: number | null,
    sales5_fee: float,
    sales4_id: number | null,
    sales4_fee: float,
    sales3_id: number | null,
    sales3_fee: float,
    sales2_id: number | null,
    sales2_fee: float,
    sales1_id: number | null,
    sales1_fee: float,
    sales0_id: number | null,
    sales0_fee: float,
    custom_id: number,
    custom_fee: float,
    trx_fee: float,
    hold_fee: float,
    mid: string,
    cat_id: string,
    //    
    module_type: number,
    pg_id: number,
    pmod_id: number,
    ps_id: number,
    pay_cond_id: number,
    terminal_id: number,
    //
    ps_fee: Float,
    pay_cond_fee: float,
    terminal_fee: float,
    //
    trx_dt: Date,
    trx_tm: Date,
    cxl_dt: Date,
    cxl_tm: Date,
    is_cancel: boolean,
    amount: number,
    ord_num: string,
    trx_id: string,
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
    item_nm: string,
    mid: string,
    cat_id: string,
    ord_num: string,
    trx_id: string,
    ori_trx_id: string,
    pay_cond_id: number,
    pay_cond_fee: float,
    
    card_nm: string,
    card_num: string,
    installment: string,
    danger_type: number,
    danger_check: boolean,
    pg_id: number,
    pg_name: string,
    ps_id: number,
    ps_name: string,
    trx_dttm: Date,
    cxl_dttm: Date,
    is_cancel:boolean,
    updated_at:datetime,
    created_at: datetime,
}
export interface FailTransaction {
    id: number,
    mcht_id: number,
    pg_id: number,
    ps_id: number,
    pmod_id: number,
    pay_cond_id: number,
    trx_dt: Date,
    trx_tm: Time,
    amount: number,
    resuld_cd: string,
    result_msg: string,
}

export interface Settle extends Bank {
    sales_amount: number,
    total_profit: number,

    appr_count: number,
    appr_amount: number,
    appr_trx_fee: number,
    appr_dpst_fee: number,
    appr_holding_fee: number,

    cxl_count: number,
    cxl_amount: number,
    cxl_trx_fee: number,
    cxl_dpst_fee: number,
    cxl_holding_fee: number,
    
    deduction_amount: number,         // 차감완료 금액
    deduction_extra_amount:number,    // 추가 차감금
    deduction_complate_amount:number, // 차감 완료금        

    comm_amount:number,         // 통신비
    settle_amount:number,       // 정산금액
    deposit_amount:number,      // 입금금액
    transfer_amount: string,    // 이체금액

    sector: string,
    rep_nm: string,
    phone_num: string,
    resident_num: string,
    business_num: string,
    addr: string,
}
export interface SettleMerchandise extends Settle{
    id: number,
    mcht_id: number,
    mcht_name: string,
    sales_id: number,
    sales_name: string,
}

export interface SettleSalesforce extends Settle{
    id: number,
    sales_id: number,
    sales_name: string,
    parent_id: number,
    parent_name: string,
}

export interface SettlesHistories extends Bank{
    id: number,
    settle_amount: number,
    settle_dt: Date,
    deposit_dt: Date,
    apply_s_dt: Date,
    apply_e_dt: Date,
    status: Boolean,
}

export interface SettlesHistoriesMerchandise {
    mcht_id: number
    mcht_name: string,
}

export interface SettlesHistoriesSalesforce {
    sales_id: number
    sales_name: string,
}

export interface Post {
    id: number,
    title: string,
    content: string,
    type:number,
    writer: string,
    parent_id: number | null,
}

export interface Complaint {
    id: number,
    mcht_id: number | null,
    mcht_name: string | null,
    tid: string,
    cust_nm: string,
    appr_dt: Date | null,
    appr_num: string,
    phone_num: string,
    hand_cust_nm: string,
    hand_phone_num: string,
    issuer_id: number | null,
    pg_id: number | null,
    pg_name: string | null,
    type: number | null,
    entry_path: string,
    is_deposit: boolean,
    note: string,
}

export interface FeeChangeHistory {
    id: number,
    mcht_id: number,
    bf_trx_fee: float,
    aft_trx_fee: float,
    created_at: Date,
    change_status: boolean,
}

export interface MchtFeeChangeHistory extends FeeChangeHistory {
    bf_hold_fee: float,
    aft_hold_fee: float,    
}


export interface SalesFeeChangeHistory extends FeeChangeHistory {
    bf_sales_id: number,
    aft_sales_id: number,
    level: number,
}

export interface NotiSendHistory {
    id: number,
    trans_id: number,
    http_code: number,
    retry_count: number,
    message: string,
    send_url: string,
    created_at: datetime | null,
}
